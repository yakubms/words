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
        $user = auth()->user();

        return [ 'projects' => $user->projects,
                'level' => $user->userWordLevel()
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Project $project)
    {
        $user = auth()->user();
        $attribute = $request->validate([
            'name' => 'max:255',
            'size' => 'integer|min:50|max:500'
        ]);

        $bookNumber = $user->projects->count() + 1;

        $name = $attribute['name'] ?? 'Book' . $bookNumber;

        $project->create([
            'owner_id' => $user->id,
            'name' => $name,
            'size' => $attribute['size']
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
        $user = auth()->user();
        if (! $user->hasProject($id)) {
            abort(403);
        }

        $data = $this->getTasksRaws($id);
        $collection = $this->convertToCollection($data);

        return ['tasks' => $collection];
    }

    /**
     * get user's project name by project id.
     * @param  int $id [description]
     * @return array  [description]
     */
    public function getName($id)
    {
        $user = auth()->user();
        if (! $user->hasProject($id)) {
            abort(403);
        }

        return ['book' => $user->projects->find($id)->name];
    }

    /**
     * get task's information by project id
     * @param  int $id project id
     * @return collection [description]
     */
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

    /**
     * convert to collection
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
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

    /**
     * [extractDefinitions description]
     * @param  [type] $words [description]
     * @return [type]        [description]
     */
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

    /**
     * [mergeDefinitions description]
     * @param  [type] $word          [description]
     * @param  [type] $enDefinitions [description]
     * @param  [type] $jpDefinitions [description]
     * @param  [type] $number        [description]
     * @return [type]                [description]
     */
    public function mergeDefinitions($word, $enDefinitions, $jpDefinitions, $number)
    {
        return collect($word)->except('def', 'lang')
                ->merge(['defs_en' => $enDefinitions,
                        'defs_jp' => $jpDefinitions,
                        'no' => $number]);
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
        $user = auth()->user();
        $attribute = $request->validate([
            'active' => 'required|integer',
        ]);

        return (string) $user->setActiveProject($attribute['active']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Project $project)
    {
        $user = auth()->user();
        $attributes = $request->validate([
            'projects' => 'required|array',
            'projects.*' => 'required|integer'
        ]);
        $project->whereOwnerId($user->id)->whereIn('id', $attributes['projects'])->delete();
        return ['projects' => $user->projects];
    }
}
