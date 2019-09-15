<?php

namespace App\Http\Controllers;

use App\Word;
use Illuminate\Http\Request;

class WordsQuizController extends Controller
{
    const LEVEL_MAX = 556;

    public function show($lemma, Word $word)
    {
        $examples = $word->getEnExamples($lemma);
        if (!$examples) {
            return [ 'error' => 'not found' ];
        }
        $level = (int) floor($word->level($lemma) / 10) + 1;
        // need validation, trimming...
        return ['level' => $level,
                'examples' => $examples];
    }

    public function generate(Request $request, Word $word)
    {
        // need validation
        $level = (int) $request->level;
        $language = $request->language;

        $minLevel = ($level - 10 > 0) ? $level - 10 : 1;
        $maxLevel = ($level + 10 < self::LEVEL_MAX) ? $level + 10 : self::LEVEL_MAX;
        $words = $word->randomWords(10, $minLevel, $maxLevel);

        $quiz = $words->map(function ($word) use ($language, $level) {
            $definition = $this->getDefinition($word, $language);
            $dummies = $this->generateDummies($word, 5, $language, $level);
            return [
                'id' => 0,
                'lemma' => $word->lemma,
                'level' => $word->level,
                'quiz' => $dummies->push($definition)->shuffle()];
        });

        return ['questions' => $quiz];
    }

    public function score(Request $request, Word $word)
    {
        // need validation
        $level = $request->level;
        $answers = $request->answers;

        $score = $this->estimate($level, $answers, $word);

        return ['level' => $score];
    }

    public function estimate($level, $answers, Word $word)
    {
        $score = $level;
        foreach ($answers as $answer) {
            $score += $this->calculateScore($answer, $level, $word);
            // dump($score);
        }
        if ($score > 0) {
            return (int) $score;
        }
        return 1;
    }

    public function calculateScore($answer, $level, $word)
    {
        if (is_null($answer)) {
            return -3;
        }
        $diff = $word->level($answer['lemma']) - $level;
        if ($this->isCorrect($word, $answer)) {
            return $this->onCorrect($diff);
        }
        return $this->onIncorrect($diff);
    }

    public function onCorrect($diff)
    {
        if ($diff < 0) {
            return 3;
        }
        return (int) ($diff / 3);
    }

    public function onIncorrect($diff)
    {
        if ($diff < 0) {
            return - (int) (abs($diff) * 0.8);
        }
        return - (int) ($diff / 3);
    }

    public function quiz(Request $request, Word $word)
    {
        // require validation
        $user = auth()->user();
        $projectId = $request->book;
        $range = $this->getRange($request->range);

        $questions = $request->questions;
        $language = $request->lang;
        $choices = $request->choices;

        $level = $request->level;
        $tasks = $this->getTasks($user, $projectId);

        if (! $tasks || ! $tasks->count()) {
            return ['error' => '単語帳に単語が登録されていません。'];
        }
        $tasks = $tasks->whereIn('is_complete', $range)
                        ->random($questions);

        $quiz = $this->getQuiz($tasks, $word, $language, $level, $choices);

        return ['questions' => $quiz];
    }

    public function check(Request $request, Word $word)
    {
        // need validation
        $user = auth()->user();
        $answers = $request->answers;

        $results = [];
        foreach ($answers as $answer) {
            $wordByLemma = $word->whereLemma($answer['lemma'])->first();
            $results[] = [
            'id' => $answer['id'],
            'level' => $wordByLemma->level,
            'meaning' => $wordByLemma->enExamples(),
            'meaning_jp' => $wordByLemma->jpDefinitions(),
            'lemma' => $answer['lemma'],
            'isCorrect' => $this->isCorrect($word, $answer),
            'isComplete' => $user->hasComplete($answer['id'])
            ];
        }

        return ['results' => $results];
    }

    public function isCorrect($word, $answer)
    {
        return $word->isCorrect($answer['lemma'], $answer['answer']);
    }

    public function getRange($range)
    {
        switch ($range) {
            case 'ongoing':
                return [0];
            case 'complete':
                return [1];
            default:
                return [0, 1];
        }
    }

    public function getTasks($user, $id)
    {
        if ($id == 'all') {
            return $user->tasks;
        }
        return $user->projects->find($id)->tasks;
    }

    public function getQuiz($tasks, $word, $language, $level, $choices)
    {
        return $tasks->map(function ($task) use ($word, $tasks, $language, $level, $choices) {
            $definition = $this->getDefinition($task, $language);
            $dummies = $this->generateDummies($word, $choices - 1, $language, $level);
            return [
                'id' => $task->id,
                'lemma' => $task->lemma,
                'level' => $task->level,
                'quiz' => $dummies->push($definition)->shuffle()];
        });
    }

    public function getDefinition($task, $language)
    {
        if ($language == 'eng') {
            return $task->enDefinitions()->first();
        }
        return $task->jpDefinitions()->first();
    }

    public function generateDummies($word, $choices, $language, $level)
    {
        return $word->generateDummies($choices, $language, $level);
    }
}
