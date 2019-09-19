<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = ['project_id', 'word_id', 'is_complete'];
    protected $appends = ['level', 'lemma'];
    protected $hidden = ['project_id', 'laravel_through_key'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function word()
    {
        return $this->belongsTo(Word::class, 'word_id', 'wordid');
    }

    public function user()
    {
        return $this->project->user;
    }

    public function allTasks($userId, $projectId)
    {
        if ($projectId === 'all') {
            return $this->with([
                'word',
                'project' => function ($query) use ($userId) {
                    $query->withTrashed();
                }])
            ->whereHas('project', function ($query) use ($userId) {
                $query->where('owner_id', $userId);
            })->get();
        }

        return $this->with(['word', 'project'])
            ->whereHas('project', function ($query) use ($userId, $projectId) {
                $query->where('owner_id', $userId)
                    ->where('project_id', $projectId);
            })->get();
    }

    public function getLemmaAttribute()
    {
        return $this->word->lemma;
    }

    public function getLevelAttribute()
    {
        return $this->word->level;
    }

    public function enDefinitions()
    {
        return $this->word->enDefinitions();
    }
    public function jpDefinitions()
    {
        return $this->word->jpDefinitions();
    }
}
