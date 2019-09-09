<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['owner_id', 'name', 'size'];
    protected $appends = ['task_count', 'task_complete_count'];
    protected $hidden = ['tasks'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getTaskCountAttribute()
    {
        return $this->tasks->count();
    }

    public function getTaskCompleteCountAttribute()
    {
        return $this->tasks->where('is_complete')->count();
    }
}
