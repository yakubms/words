<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Word extends Model
{
    public $table = 'word';
    public $incrementing = true;
    protected $primaryKey = 'wordid';

    public function senses()
    {
        return $this->hasMany(Sense::class, 'wordid');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function wordId($lemma)
    {
        $word = $this->whereLemma($lemma);
        if (!$word) {
            return false;
        }
        return $word->wordid;
    }

    public function whereLemma($lemma)
    {
        try {
            return $this->where('lemma', $lemma)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return false;
        }
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

    public function getLevel($lemma)
    {
        $lemma = $this->whereLemma($lemma);
        if (! $lemma) {
            return false;
        }
        return $lemma->level;
    }

    public function getSynsets($searchWord)
    {
        $lemma = $this->whereLemma($searchWord);
        if (! $lemma) {
            return false;
        }
        return $lemma->senses
            ->flatMap(function ($sense) {
                return $sense->synsets;
            });
    }

    public function getDefinitions($lemma, $lang)
    {
        $synsets = $this->getSynsets($lemma);
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
