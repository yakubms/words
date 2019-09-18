<?php

namespace App\Http\Controllers;

use App\Task;
use App\Word;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, Word $word)
    {
        $user = auth()->user();

        $request->validate([
            'file' => 'required|file|mimes:txt,csv|max:1000'
        ]);

        $this->debug_time();

        $content = $request->file->get();
        $json = json_decode($request->input('file'));
        [$allowDuplicate, $asComplete] = $this->getUploadOptions($json);

        $this->debug_time();
        // too slow why??
        $lemmas = $this->filterLemmas($content, $user, $word, $allowDuplicate);

        $this->debug_time();
        $array = $this->processLemmas($lemmas, $asComplete, $user, $word);

        $this->debug_time();
        if (\DB::table('tasks')->insert($array) === false) {
            return ['errors' => 'upload failed'];
        }
        $this->debug_time();
        return ['count' => count($array)];
    }

    public function getUploadOptions($json)
    {
        if (!$json) {
            return [false, false];
        }

        $allowDuplicate = (bool) $json->allowDuplicate;
        $asComplete = (bool) $json->asComplete;

        return [$allowDuplicate, $asComplete];
    }

    /**
     * exclude duplicates and incorrect words from array
     * @param  array $lemmas from user txt file
     * @return Collection $collection
     */
    public function filterLemmas($content, $user, $word, $allowDuplicate)
    {
        $lemmas = preg_split('/(\r?\n|\s+,\s+)/', trim($content));

        $this->debug_time();

        return collect($lemmas)
            ->filter(function ($lemma) use ($user, $word, $allowDuplicate) {
                return $this->duplicateFilter($user, $lemma, $word, $allowDuplicate);
            })->unique()->values();
    }

    /**
     * exclude duplicate words
     * @param  [type] $user           [description]
     * @param  [type] $lemma          [description]
     * @param  [type] $word           [description]
     * @param  [type] $allowDuplicate [description]
     * @return [type]                 [description]
     */
    public function duplicateFilter($user, $lemma, $word, $allowDuplicate)
    {
        if ($allowDuplicate) {
            return $word->exists($lemma);
        }

        return !$user->hasDuplicateWord($word->id($lemma)) and $word->exists($lemma);
    }

    /**
     * create a new book when no book is available
     * @param  [type] $user      [description]
     * @param  [type] $projectId [description]
     * @return [type]            [description]
     */
    public function createNextProjectWhenFull($user, $projectId)
    {
        if (!$projectId) {
            $projectId = $user->createProject();
        }
        return $projectId;
    }

    public function processLemmas($lemmas, $asComplete, $user, $word)
    {
        $projectId = $user->activeProjectId();
        $projectId = $this->createNextProjectWhenFull($user, $projectId);

        $room = $user->getProjectRoom($projectId);
        $now = \Carbon\Carbon::now();

        $array = [];
        foreach ($lemmas as $lemma) {
            if ($room < 1) {
                $projectId = $user->nextActiveProjectId($projectId);
                $projectId = $this->createNextProjectWhenFull($user, $projectId);
                $room = $user->getProjectRoom($projectId);
            }

            $array[] = [
                    'project_id' => $projectId,
                    'word_id' => $word->id($lemma),
                    'is_complete' => $asComplete,
                    'created_at' => $now,
                    'updated_at' => $now
            ];
            $room--;
        }

        return $array;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Task $task, Word $word)
    {
        $user = auth()->user();

        $attributes = $request->validate([
            'lemma' => 'required|max:255',
        ]);

        $lemma = $attributes['lemma'];

        $activeProject = $user->activeProjectId();
        if (!$activeProject) {
            $activeProject = $this->createNextProjectWhenFull($user, $activeProject);
        }

        $wordId = $word->id($lemma);

        // $user->hasDuplicateWord($wordId);
        // $user->saveLemma($lemma)
        Task::create([
            'project_id' => $activeProject,
            'word_id' => $wordId,
            'is_complete' => 0
        ]);

        return 'done';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show($lemma, Word $word)
    {
        $user = auth()->user();

        return (string) $user->hasDuplicateWord($word->id($lemma));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        $user = auth()->user();
        $attributes = $request->validate([
            'words' => 'required|array',
            'words.*' => 'required|integer'
        ]);
        $user->tasks()->whereIn('tasks.id', $attributes['words'])->update([
            'is_complete' => $request->isComplete
        ]);

        return 'done';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Task $task)
    {
        $user = auth()->user();

        $attributes = $request->validate([
            'tasks' => 'required|array',
            'tasks.*' => 'required|integer'
        ]);

        $user->tasks()->whereIn('tasks.id', $attributes['tasks'])->delete();
        return 'done';
    }

    public function revert()
    {
        return;
    }

    public function debug_time()
    {
        $debug = current(debug_backtrace());

        static $start_time = 0;
        static $pre_debug = null;
        static $pre_time = 0;

        $time = microtime(true);
        if (!$start_time) {
            $start_time = $time;
        }

        if ($pre_time) {
            echo sprintf(
                '<div>[%s(%d) - %s(%d)]: %d ms(ttl:%d ms)</div>',
                basename($pre_debug['file']),
                $pre_debug['line'],
                basename($debug['file']),
                $debug['line'],
                ($time * 1000 - $pre_time * 1000),
                ($time * 1000 - $start_time * 1000)
        );
        }

        $pre_debug = $debug;
        $pre_time = $time;
    }
}
