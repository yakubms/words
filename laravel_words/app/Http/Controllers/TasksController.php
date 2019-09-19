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
    public function create(Request $request, Word $word, Task $task)
    {
        $user = auth()->user();

        $request->validate([
            'file' => 'required|file|mimes:txt,csv|max:1000'
        ]);

        $content = $request->file->get();
        $json = json_decode($request->input('file'));
        [$allowDuplicate, $asComplete] = $this->getUploadOptions($json);

        $words = $word->whereLang('eng')->pluck('wordid', 'lemma');
        $tasks = $task->with(['project' => function ($query) {
            $query->withTrashed();
        }, 'word'])->get();
        $lemmas = $tasks->filter(function ($task) use ($user) {
            return $task->project->owner_id == $user->id;
        })->unique('lemma')->pluck('lemma');

        $filteredLemmas = $this->filterLemmas($content, $words, $tasks, $lemmas, $allowDuplicate);

        $array = $this->processLemmas($filteredLemmas, $asComplete, $user, $words);

        if (\DB::table('tasks')->insert($array) === false) {
            return ['errors' => 'upload failed'];
        }

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
    public function filterLemmas($content, $words, $tasks, $lemmas, $allowDuplicate)
    {
        $data = preg_split('/(\r?\n|\s+,\s+)/', trim($content));

        return collect($data)
            ->filter(function ($datum) use ($words, $lemmas, $allowDuplicate) {
                return $this->duplicateFilter($datum, $words, $lemmas, $allowDuplicate);
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
    public function duplicateFilter($datum, $words, $lemmas, $allowDuplicate)
    {
        if ($allowDuplicate) {
            return $words->has($datum);
        }

        return $words->has($datum) and !$lemmas->contains($datum);
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

    public function processLemmas($lemmas, $asComplete, $user, $words)
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
                    'word_id' => $words->get($lemma),
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
}
