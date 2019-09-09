<?php

namespace App\Http\Controllers;

use App\Word;
use Illuminate\Http\Request;

class WordsQuizController extends Controller
{
    public function show($lemma, Word $word)
    {
        // need validation, trimming...
        return $word->getEnExamples($lemma) ?: [ 'error' => 'not found' ];
    }

    public function generate(Request $request, Word $word)
    {
        // need validation
        $level = $request->level;
        $language = $request->language;

        $collection = $word->betweenLevel($level, 20);

        $quiz = $collection->random(10)->map(function ($item) use ($collection, $language) {
            if ($language == 'eng') {
                $definition = $item->getEnDefinitions($item->lemma)->first();
            } else {
                $definition = $item->getJpDefinitions($item->lemma)->first();
            }
            $dummies = $collection->where('lemma', '<>', $item->lemma)->random(5)
                ->map(function ($el) use ($language) {
                    if ($language == 'eng') {
                        return $el->getEnDefinitions($el->lemma)->first();
                    }
                    return $el->getJpDefinitions($el->lemma)->first();
                });
            return [
                'lemma' => $item->lemma,
                'level' => $item->level,
                'quiz' => $dummies->push($definition)->shuffle()];
        });

        return $quiz;
    }

    public function score(Request $json, Word $word)
    {
        // need validation later...
        if (! $json->isJson()) {
            return 'invalid request';
        }
        $collection = collect($json);
        $level = $collection['level'];
        $answers = $collection['answers'];

        $score = $this->estimate($level, $answers, $word);

        return $score;
    }

    public function estimate($level, $answers, Word $word)
    {
        $score = $level;
        foreach ($answers as $answer) {
            var_dump($score);
            if (is_null($answer)) {
                $score -= 3;
                continue;
            }
            $diff = $word->getLevel($answer['lemma']) - $level;
            if ($this->isCorrect($word, $answer)) {
                $score = $this->onCorrect($diff, $score);
                // if ($diff < 0) {
                //     $score += 1;
                //     continue;
                // }
                // $score += $diff / 5;
                continue;
            }
            $score = $this->onIncorrect($diff, $score);

            // if ($diff < 0) {
            //     $score -= abs($diff) * 2;
            //     continue;
            // }
            // $score -= $diff / 3;
        }
        if ($score > 0) {
            return (int) $score;
        }
        return 1;
    }

    public function isCorrect($word, $answer)
    {
        return $word->isCorrect($answer['lemma'], $answer['answer']);
    }

    public function onCorrect($diff, $score)
    {
        if ($diff < 0) {
            return $score + 3;
        }
        return $score + (int) ($diff / 3);
    }

    public function onIncorrect($diff, $score)
    {
        if ($diff < 0) {
            return $score - (int) (abs($diff) * 0.8);
        }
        return $score - (int) ($diff / 3);
    }
}
