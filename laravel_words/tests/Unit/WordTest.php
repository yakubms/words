<?php

namespace Tests\Unit;

use App\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WordTest extends TestCase
{
    /** @test */
    public function it_returns_a_word_level()
    {
        $word = new Word();
        $level = $word->level('gratify');
        $this->assertEquals(271, $level);

        $level = $word->level('hogehoge');
        $this->assertFalse($level);
    }

    /** @test */
    public function it_returns_word_definitions()
    {
        $word = new Word();
        $definitions = $word->lemma('gratify')->enDefinitions();
        $this->assertCount(3, $definitions);

        $definitions = $word->lemma('gratify')->jpDefinitions();
        $this->assertCount(3, $definitions);

        $definitions = $word->lemma('gratify')->enExamples();
        $this->assertCount(2, $definitions);
    }
}
