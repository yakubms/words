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

    public function lemma($lemma)
    {
        return $this->whereLemma($lemma)->first();
    }

    public function id($lemma)
    {
        return $this->lemma($lemma)->wordid;
    }

    public function enDefinitions()
    {
        return $this->synsets
            ->where('lang', 'eng')
            ->pluck('def');
    }

    public function enExamples()
    {
        return $this->synsets
            ->where('lang', 'ENG')
            ->pluck('def');
    }

    public function jpDefinitions()
    {
        return $this->synsets
            ->where('lang', 'jpn')
            ->pluck('def');
    }

    public function definition($lang)
    {
        if ($lang == 'eng') {
            return $this->enDefinitions()->first();
        }
        return $this->jpDefinitions()->first();
    }

    public function generateDummies($choices, $language, $level)
    {
        return $this->whereLevel($level)
            ->inRandomOrder()->take($choices)->get()
            ->map(function ($el) use ($language) {
                return $el->definition($language);
            });
    }

    public function wordIds($lemma)
    {
        $word = $this->whereLemma($lemma);
        if (!count($word)) {
            return false;
        }
        return $word->get('wordid');
    }

    public function randomWords($number, $minLevel, $maxLevel)
    {
        return $this->whereBetween('level', [$minLevel, $maxLevel])->inRandomOrder()->take($number)->get();
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
        return trim($this->getEnDefinitions($lemma)->first()) == $answer;
    }
}
