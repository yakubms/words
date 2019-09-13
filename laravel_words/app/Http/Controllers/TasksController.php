<?php

namespace App\Http\Controllers;

use App\Task;

use App\Word;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, Word $word)
    {
        $user = auth()->user();

        $request->validate([
            'file' => 'required|file|max:1000'
        ]);
        // require validation
        // $name = $request->filepond->getClientOriginalName();
        // return $request->all();
        // return 'hogee';
        // return $request->file->guessExtension();
        // dd($request);
        $content = $request->file->get();
        $json = json_decode($request->input('file'));
        // dd($json);
        $allowDuplicate = $json->allowDuplicate;
        $asComplete = $json->asComplete;
        // dd($allowDuplicate, $asComplete);
        $lemmas = preg_split('/(\r?\n|\s+,\s+)/', trim($content));
        // return $lemmas;
        // (option) exclude duplicate words

        $lemmas = $this->filterLemmas($lemmas, $user, $word, $allowDuplicate);

        // return $lemmas;
        $projectId = $user->activeProjectId();
        $projectId = $this->createNextProjectWhenFull($user, $projectId);

        $array = [];
        // $count = count($lemmas)
        $room = $user->getProjectRoom($projectId);
        $now = \Carbon\Carbon::now();
        foreach ($lemmas as $lemma) {
            if ($room < 1) {
                $projectId = $user->nextActiveProjectId($projectId);
                $projectId = $this->createNextProjectWhenFull($user, $projectId);
                $room = $user->getProjectRoom($projectId);
            }

            $array[] = [
                    'project_id' => $projectId,
                    'word_id' => $word->wordId($lemma),
                    'is_complete' => $asComplete,
                    'created_at' => $now,
                    'updated_at' => $now
            ];
            $room--;
        }

        $count = count($array);

        if (\DB::table('tasks')->insert($array) === false) {
            return ['error' => 'upload failed'];
        }
        return ['count' => $count];
    }

    /**
     * exclude duplicates and incorrect words from array
     * @param  array $lemmas from user txt file
     * @return Collection $collection
     */
    public function filterLemmas($lemmas, $user, $word, $allowDuplicate = false)
    {
        return collect($lemmas)
            ->filter(function ($lemma) use ($user, $word, $allowDuplicate) {
                // return !$user->hasDuplicateWord($lemma) and $word->whereLemma($lemma);
                return $this->duplicateFilter($user, $lemma, $word, $allowDuplicate);
            })->unique()->values();
    }

    public function duplicateFilter($user, $lemma, $word, $allowDuplicate)
    {
        if ($allowDuplicate) {
            return $word->whereLemma($lemma);
        }
        return !$user->hasDuplicateWord($lemma) and $word->whereLemma($lemma);
    }

    public function createNextProjectWhenFull($user, $projectId)
    {
        if (!$projectId) {
            $projectId = $user->createProject();
        }
        return $projectId;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Task $task, Word $word)
    {
        // require validation
        $user = auth()->user();
        $lemma = $request->lemma;

        $activeProject = $user->activeProjectId();
        if (!$activeProject) {
            $activeProject = $this->createNextProjectWhenFull($user, $activeProject);
        }

        $wordId = $word->wordId($lemma);

        // $user->hasDuplicateWord($wordId);

        // $user->saveLemma($lemma)
        Task::create([
            'project_id' => $activeProject,
            'word_id' => $wordId,
            'is_complete' => 0
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show($lemma)
    {
        // require validation
        $user = auth()->user();

        return (string) $user->hasDuplicateWord($lemma);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        //
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
        //require validation
        $user = auth()->user();
        // $words = $request->words;
        // if (! $user->hasTask($id)) {
        //     abort(403);
        // }
        $user->tasks()->whereIn('tasks.id', collect($request->words))->update([
            'is_complete' => $request->isComplete
        ]);

        return 'done';
        // $task->find($id)->update([
        //     'is_complete' => $request->isComplete
        // ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Task $task)
    {
        // need validation
        $user = auth()->user();

        $user->tasks()->whereIn('tasks.id', collect($request))->delete();
        // $user->projects->destroy(collect($request));
        return 'done';
    }
}
