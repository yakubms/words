<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Word extends Model
{
    protected $table = 'word';
    public $incrementing = true;
    protected $primaryKey = 'wordid';
    // protected $appends = ['en_definition', 'jp_definition'];

    public function senses()
    {
        return $this->hasMany(Sense::class, 'wordid');
    }

    public function synsets()
    {
        return $this->hasManyThrough(
            Synset::class,
            Sense::class,
            'wordid',
            'synset',
            'wordid',
            'synset'
        );
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    // public function getEnDefinitionAttribute()
    // {
    //     return $this->synsets->where('lang', 'ENG')->pluck('def');
    // }

    // public function getJpDefinitionAttribute()
    // {
    //     return $this->synsets->where('lang', 'jpn')->pluck('def');
    // }

    public function wordIds($lemma)
    {
        $word = $this->whereLemma($lemma);
        if (!count($word)) {
            return false;
        }
        return $word->get('wordid');
    }

    public function betweenLevel($level, $diff)
    {
        $minLevel = $level - $diff > 0 ? $level - $diff : $level;
        $maxLevel = $level + $diff;

        try {
            return $this->whereBetween('level', [$minLevel, $maxLevel])->get(['lemma', 'level']);
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    public function level($lemma)
    {
        $lemma = $this->whereLemma($lemma)->first();
        if (! $lemma) {
            return false;
        }
        return $lemma->level;
    }

    public function getSynsetsByLemma($lemma)
    {
        $lemmas = $this->whereLemma($lemma)->get();
        if (! count($lemmas)) {
            return false;
        }
        return $lemmas
            ->flatMap(function ($lemma) {
                return $lemma->synsets;
            });
    }

    public function getDefinitions($lemma, $lang)
    {
        $synsets = $this->getSynsetsByLemma($lemma);
        if (! $synsets) {
            return false;
        }
        return $synsets->filter(function ($synset) use ($lang) {
            return $synset->lang == $lang;
        })->values()->pluck('def');
    }

    public function getEnDefinitions($lemma)
    {
        return $this->getDefinitions($lemma, 'eng');
    }

    public function getEnExamples($lemma)
    {
        return $this->getDefinitions($lemma, 'ENG');
    }

    public function getJpDefinitions($lemma)
    {
        return $this->getDefinitions($lemma, 'jpn');
    }

    public function isCorrect($lemma, $answer)
    {
        return $this->getEnDefinitions($lemma)->first() == $answer;
    }
}
