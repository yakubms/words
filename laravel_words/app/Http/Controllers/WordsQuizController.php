<?php

namespace App\Http\Controllers;

use App\Word;
use Illuminate\Http\Request;

class WordsQuizController extends Controller
{
    public function generate(Request $request, Word $word)
    {
        // need validation
        $level = $request->level;

        $collection = $word->betweenLevel($level, 20);

        $quiz = $collection->random(10)->map(function ($item) use ($collection) {
            $definition = $item->getEnDefinitions($item->lemma)->first();
            $dummies = $collection->where('lemma', '<>', $item->lemma)->random(4)
                ->map(function ($el) {
                    return $el->getEnDefinitions($el->lemma)->first();
                });
            return [
                'lemma' => $item->lemma,
                'level' => $item->level,
                'quiz' => $dummies->push($definition)->shuffle()];
        });

        return $quiz;
    }

    // public function score(Request $request, Word $word)
    // {
    //     $answers = $request->answers;

    //     foreach ($answers as $answer) {
    //         $word->isCorrect($answer);
    //     }

    //     return $score;
    // }

    // public function estimate($current, Word $word)
    // {
    //     $score = $current;
    //     foreach ($answers as $answer) {
    //         $diff = $word->level - $current;
    //         if ($answer->isCorrect()) {
    //             if ($diff < 0) {
    //                 $score += 1;
    //                 continue;
    //             }
    //             $score += $diff / 5;
    //             continue;
    //         }

    //         if ($diff < 0) {
    //             $score -= abs($diff) * 2;
    //         }
    //         $score -= $diff / 3;
    //     }

    //     return $score;
    // }
}
