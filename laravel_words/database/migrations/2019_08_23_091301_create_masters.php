<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasters extends Migration
{
    const SQL_PATH = 'database/worddata/dump.sql';

    /**
     * You must prepare the wordnetjp sql file for this migration.
     *
     * @return void
     */
    public function up()
    {
        $this->buildWordSql();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ancestor');
        Schema::dropIfExists('link_def');
        Schema::dropIfExists('pos_def');
        Schema::dropIfExists('sense');
        Schema::dropIfExists('synlink');
        Schema::dropIfExists('synset');
        Schema::dropIfExists('synset_def');
        Schema::dropIfExists('synset_ex');
        Schema::dropIfExists('variant');
        Schema::dropIfExists('word');
        Schema::dropIfExists('xlink');
    }

    /**
     * import worddb
     * @param  [type] $file [description]
     * @return [type]       [description]
     */
    private function buildWordSql()
    {
        echo "It takes about a few hours to import WordNet DB. Please wait patiently.\n";
        echo "Processing...\n";

        if (PHP_OS == 'WINNT') {
            $command = 'mysql --user=' . config('database.connections.mysql.username') . ' --password=' . config('database.connections.mysql.password') . ' --database=' . config('database.connections.mysql.database') . '--execute="source ' . base_path(self::SQL_PATH);
        } else {
            $command = 'mysql -u '. config('database.connections.mysql.username') . ' -h ' . config('database.connections.mysql.host') . ' -p' . config('database.connections.mysql.password') . ' ' . config('database.connections.mysql.database') . ' < ' . base_path(self::SQL_PATH);
        }
        exec($command);
        echo "Done.\n";
    }
}
