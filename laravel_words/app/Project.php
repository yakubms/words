<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = ['owner_id', 'name', 'size', 'is_active'];
    protected $appends = ['task_count', 'task_complete_count'];
    protected $hidden = ['tasks'];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

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

    public function room()
    {
        return $this->size - $this->tasks->count();
    }
}
