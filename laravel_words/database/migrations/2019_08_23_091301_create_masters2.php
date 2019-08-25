<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasters2 extends Migration
{
    const FREQUENCY_PATH = 'database/worddata/frequency.json';
    const CHUNK = 10000;
    /**
     * You must prepare the frequency json for this migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('word', function (Blueprint $table) {
            $table->integer('freqCount')->after('lemma')->nullable();
            $table->integer('cdCount')->after('freqCount')->nullable();
            $table->timestamps();
        });

        Schema::table('sense', function (Blueprint $table) {
            $table->bigIncrements('senseid')->first();
        });

        Schema::table('synset_def', function (Blueprint $table) {
            $table->bigIncrements('synsetid')->first();
        });

        $data = json_decode(file_get_contents(base_path(self::FREQUENCY_PATH)), true);
        echo "adding frequency information...\n";

        $word = new \App\Word();
        $array = [];
        foreach ($data as $lemma => $frequency) {
            $array[] = array_merge(['lemma' => $lemma], $frequency);
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
            $table->dropColumn('freqCount');
            $table->dropColumn('cdCount');
            $table->dropColumn('updated_at');
            $table->dropColumn('created_at');
        });

        Schema::table('sense', function (Blueprint $table) {
            $table->dropColumn('senseid');
        });

        Schema::table('synset_def', function (Blueprint $table) {
            $table->dropColumn('synsetid');
        });
    }
}
