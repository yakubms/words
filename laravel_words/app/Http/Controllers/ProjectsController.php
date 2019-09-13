<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // require validation
        $user = auth()->user();

        return [ 'projects' => $user->projects,
                'level' => $user->userWordLevel()
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Project $project)
    {
        // require validation
        $user = auth()->user();

        $bookNumber = $user->projects->count() + 1;
        $name = $request->name ?? 'Book' . $bookNumber;

        $project->create([
            'owner_id' => $user->id,
            'name' => $name,
            'size' => $request->size
        ]);

        return ['projects' => $user->refresh()->projects];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show($id, Project $project)
    {
        // require validation
        $user = auth()->user();
        if (! $user->hasProject($id)) {
            abort(403);
        }

        $data = $this->getTasksRaws($id);
        $collection = $this->convertToCollection($data);

        return ['tasks' => $collection];
    }

    public function getName($id)
    {
        $user = auth()->user();
        if (! $user->hasProject($id)) {
            abort(403);
        }

        return ['book' => $user->projects->find($id)->name];
    }

    public function getTasksRaws($id)
    {
        return \DB::table('tasks')
            ->whereProjectId($id)
            ->join('word', 'word_id', '=', 'word.wordid')
            ->join('sense', 'word.wordid', '=', 'sense.wordid')
            ->join('synset_def', 'sense.synset', '=', 'synset_def.synset')
            ->get(['id', 'word_id', 'lemma', 'level', 'def', 'is_complete', 'synset_def.lang', 'tasks.created_at'])
            ->whereNotIn('lang', 'eng')
            ->groupBy('id');
    }

    public function convertToCollection($data)
    {
        $collection = [];
        $number = 1;
        foreach ($data as $words) {
            [$enDefinitions, $jpDefinitions] = $this->extractDefinitions($words);
            $collection[] = $this->mergeDefinitions($words->first(), $enDefinitions, $jpDefinitions, $number);
            $number++;
        }
        return $collection;
    }

    public function extractDefinitions($words)
    {
        $enDefinitions = [];
        $jpDefinitions = [];
        foreach ($words as $word) {
            if ($word->lang == 'ENG') {
                $enDefinitions[] = $word->def;
                continue;
            }
            $jpDefinitions[] = $word->def;
        }
        return [$enDefinitions, $jpDefinitions];
    }

    public function mergeDefinitions($word, $enDefinitions, $jpDefinitions, $number)
    {
        return collect($word)->except('def', 'lang')
                ->merge(['defs_en' => $enDefinitions,
                        'defs_jp' => $jpDefinitions,
                        'no' => $number]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // need validation
        $user = auth()->user();

        return (string) $user->setActiveProject($request->active);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Project $project)
    {
        // need validation
        $user = auth()->user();
        // return $request;

        $project->whereOwnerId($user->id)->whereIn('id', collect($request))->delete();
        // $user->projects->destroy(collect($request));
        return ['projects' => $user->projects];
    }
}
