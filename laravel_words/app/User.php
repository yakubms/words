<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'userid', 'email', 'password', 'api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function tasks()
    {
        return $this->hasManyThrough(
            Task::class,
            Project::class,
            'owner_id'
        );
    }

    public function hasProject($id)
    {
        if ($this->projects()->find($id)) {
            return true;
        }
        return false;
    }

    public function hasTask($id)
    {
        if ($this->tasks()->find($id)) {
            return true;
        }
        return false;
    }

    public function hasComplete($taskId)
    {
        return $this->tasks->find($taskId)->is_complete;
    }

    public function allTasks()
    {
        return $this->projects()->withTrashed()
            ->get()->flatMap(function ($project) {
                return $project->tasks;
            })->unique('word_id');
    }

    public function userWordLevel()
    {
        return (int) \DB::table('tasks')
            ->join('word', 'word_id', '=', 'word.wordid')
            ->join('projects', 'project_id', '=', 'projects.id')
            ->where('is_complete', true)
            ->where('owner_id', auth()->user()->id)
            ->orderBy('level', 'desc')
            ->take(3000)
            ->get()->avg('level');
    }

    // public function allCompleteTasks()
    // {
    //     return $this->projects()->withTrashed()
    //         ->get()->flatMap(function ($project) {
    //             return $project->tasks->where('is_complete', true);
    //         })->unique('word_id');
    // }

    // public function userWordLevel()
    // {
    //     $tasks = $this->allCompleteTasks()->sortByDesc('level');
    //     $count = $tasks->count();

    //     return (int) $tasks->take(3000)->avg('level');
    // }

    public function setActiveProject($projectId)
    {
        if ($active = $this->projects()->where('is_active', true)->first()) {
            $active->update(['is_active' => false]);
        }
        if ($project = $this->projects()->find($projectId)) {
            return $project->update(['is_active' => true]);
        }
        $active->update(['is_active' => true]);
        return false;
    }

    public function getProjectRoom($projectId)
    {
        return $this->projects->find($projectId)->room();
    }

    public function createProject()
    {
        $number = $this->projects->count() + 1;
        Project::create([
            'owner_id' => $this->id,
            'name' => 'Book' . $number,
            'size' => 500
        ]);

        return $this->refresh()->projects->last()->id;
    }

    public function activeProjectId()
    {
        if ($project = $this->projects->where('is_active')->first()) {
            return $project->id;
        }
        $project =  $this->projects->filter(function ($project) {
            return $project->size != $project->tasks->count();
        })->first();

        if ($project) {
            return $project->id;
        }

        return null;
    }

    public function nextActiveProjectId($id)
    {
        if ($this->projects->find($id)->is_active == true) {
            return $this->projects
                ->where('id', '>', $id)
                ->filter(function ($project) {
                    return $project->room() > 0;
                })->first()->id;
        }

        $next = $this->projects->where('is_active', '<>', true)
            ->where('id', '>', $id)
            ->filter(function ($project) {
                return $project->room() > 0;
            })->first();
        if ($next) {
            return $next->id;
        }
        return null;
    }

    public function hasDuplicateWord($lemma)
    {
        $word = new Word;
        return $this->tasks->contains('word_id', $word->wordId($lemma));
    }
}
