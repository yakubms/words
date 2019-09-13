<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['project_id', 'word_id', 'is_complete'];
    protected $appends = ['level', 'lemma'];
    protected $hidden = ['word', 'project_id'];

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

    public function getLemmaAttribute()
    {
        return $this->word->lemma;
    }

    public function getLevelAttribute()
    {
        return $this->word->level;
    }

    // public function getMeaningAttribute()
    // {
    //     return $this->word->enDefinitions()->first();
    // }

    // public function getMeaningJpAttribute()
    // {
    //     return $this->word()->getJpDefinitions()->first();
    // }
}
