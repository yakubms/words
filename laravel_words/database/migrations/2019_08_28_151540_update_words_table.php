<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateWordsTable extends Migration
{
    const CHUNK = 10000;

    /**
     * add word_level column to word table
     *
     * @return void
     */
    public function up()
    {
        Schema::table('word', function (Blueprint $table) {
            $table->unsignedSmallInteger('level')->after('lemma')->nullable();
        });

        $collection = $this->getFreqencyCollection();
        echo "adding word level information...\n";

        $word = new \App\Word();
        $array = [];
        foreach ($collection as $index => $data) {
            $level = (int) floor(($index + 1) / 100) + 1;
            $array[] = ['lemma' => $data->lemma, 'level' => $level];
        }
        $chunks = array_chunk($array, self::CHUNK);
        foreach ($chunks as $index => $chunk) {
            echo "chunk {$index} processing...\n";
            Batch::update($word, $chunk, 'lemma');
        }
        echo "done.\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('word', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }

    private function getFreqencyCollection()
    {
        return DB::table('word')->distinct()->orderBy('freqCount', 'desc')->whereNotNull('freqCount')->get(['lemma', 'freqCount']);
    }
}
