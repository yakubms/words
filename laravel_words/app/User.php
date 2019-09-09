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

    public function activeProject()
    {
        if ($project = $this->projects->where('is_active')->first()) {
            return $project->id;
        }
        return $this->projects->filter(function ($project) {
            return $project->size != $project->tasks->count();
        })->first()->id;
    }

    public function hasDuplicateWord($wordId)
    {
        return $this->tasks->contains('word_id', $wordId);
    }
}
