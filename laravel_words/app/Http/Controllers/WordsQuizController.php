<?php

namespace App\Http\Controllers;

use App\Word;
use Illuminate\Http\Request;

class WordsQuizController extends Controller
{
    const LEVEL_MAX = 556;

    /**
     * show a level and definitions by lemma
     * @param  [type] $lemma [description]
     * @param  Word   $word  [description]
     * @return [type]        [description]
     */
    public function show($lemma, Word $word)
    {
        $word = $word->lemma($lemma);
        if (!$word) {
            return [ 'error' => 'not found' ];
        }
        $enExamples = $word->enExamples();
        $jpDefinitions = $word->jpDefinitions();

        $level = (int) floor($word->level / 10) + 1;
        // need validation, trimming...
        return ['level' => $level,
                'examples' => $enExamples,
                'meaningsJp' => $jpDefinitions];
    }

    /**
     * generate random quiz by the requested level
     * @param  Request $request [description]
     * @param  Word    $word    [description]
     * @return [type]           [description]
     */
    public function generate(Request $request, Word $word)
    {
        $attributes = $request->validate([
            'level' => 'required|integer|min:1|max:999',
            'language' => 'required|max:3'
        ]);
        $level = $attributes['level'];
        $language = $attributes['language'];

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

    /**
     * get scores by submitted user's answer
     * @param  Request $request [description]
     * @param  Word    $word    [description]
     * @return [type]           [description]
     */
    public function score(Request $request, Word $word)
    {
        $attributes = $request->validate([
            'level' => 'required|integer|min:1|max:999',
            'answers' => 'required|array',
            'answers.*.lemma' => 'required|string|max:100',
            'answers.*.answer' => 'max:999'
        ]);

        $level = $attributes['level'];
        $answers = $attributes['answers'];

        $score = $this->estimate($level, $answers, $word);

        return ['level' => $score];
    }

    /**
     * caluculate scores
     * @param  [type] $level   [description]
     * @param  [type] $answers [description]
     * @param  Word   $word    [description]
     * @return [type]          [description]
     */
    public function estimate($level, $answers, Word $word)
    {
        $score = $level;
        foreach ($answers as $answer) {
            $score += $this->calculateScore($answer, $level, $word);
        }
        if ($score > 0) {
            return (int) $score;
        }
        return 1;
    }

    /**
     * caluculate scores
     * @param  [type] $answer [description]
     * @param  [type] $level  [description]
     * @param  [type] $word   [description]
     * @return [type]         [description]
     */
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

    /**
     * generate quiz for authenticated user
     * @param  Request $request [description]
     * @param  Word    $word    [description]
     * @return [type]           [description]
     */
    public function quiz(Request $request, Word $word)
    {
        $user = auth()->user();

        $attributes = $request->validate([
            'book' => 'required',
            'range' => 'required|string',
            'questions' => 'required|integer|max:100',
            'lang' => 'required|max:3',
            'choices' => 'required|integer|min:3|max:9',
            'level' => 'required|integer|min:1|max:999'
        ]);
        $projectId = $attributes['book'];
        $range = $this->getRange($attributes['range']);
        $questions = $attributes['questions'];
        $language = $attributes['lang'];
        $choices = $attributes['choices'];
        $level = $attributes['level'];

        $tasks = $this->getTasks($user, $projectId);

        if (! $tasks || ! $tasks->count()) {
            return ['errors' => '単語帳に単語が登録されていません。'];
        }
        $tasks = $tasks->whereIn('is_complete', $range)
                        ->random($questions);

        $quiz = $this->getQuiz($tasks, $word, $language, $level, $choices);

        return ['questions' => $quiz];
    }

    /**
     * check if user's answers are correct
     * @param  Request $request [description]
     * @param  Word    $word    [description]
     * @return [type]           [description]
     */
    public function check(Request $request, Word $word)
    {
        $user = auth()->user();

        $attributes = $request->validate([
            'answers' => 'required|array',
            'answers.*.lemma' => 'required|string|max:100',
            'answers.*.id' => 'required|integer',
        ]);

        $answers = $attributes['answers'];

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
