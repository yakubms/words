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
        $level = $word->getLevel('gratify');
        $this->assertEquals(271, $level);

        $level = $word->getLevel('hogehoge');
        $this->assertFalse($level);
    }

    /** @test */
    public function it_returns_word_definitions()
    {
        $word = new Word();
        $definitions = $word->getEnDefinitions('gratify');
        $this->assertCount(3, $definitions);

        $definitions = $word->getJpDefinitions('gratify');
        $this->assertCount(3, $definitions);

        $definitions = $word->getEnExamples('gratify');
        $this->assertCount(2, $definitions);

        $definitions = $word->getEnDefinitions('hogehoge');
        $this->assertFalse($definitions);
    }
}
