<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasters extends Migration
{
    const SQL_PATH = 'database/worddata/dump.sql';
    const CHUNK_SIZE = 30000;
    const TEXT_LOOKUP = [
        'lemma', 'synset', 'synset1', 'synset2', 'resource', 'link'
    ];
    const TEXT_INDEX_LENGTH = 100;

    private $dump;
    private $insertValues;
    private $insertCount;
    private $table;
    private $column;

    public function __construct()
    {
        $this->dump = $this->getDumpArray();
        $this->insertValues = [];
        $this->insertCount = 0;
        $this->table = '';
        $this->column = [];
    }
    /**
     * You must prepare the wordnetjp sql file(dump) for this migration.
     *
     * @return void
     */

    public function up()
    {
        echo "importing DB... It takes a minutes...\n";

        foreach ($this->dump as $index => $query) {
            $this->processQuery($query, $index);
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

    private function processQuery($query, $index)
    {
        if ($this->isOfInsert($query)) {
            $this->addInsertValues($query);
            $this->processBulkInsert($index);
            return;
        }

        if ($this->isOfCreateTable($query)) {
            $this->setTableColumn($query);
            DB::statement($query);
            return;
        }

        if ($this->isOfCreateIndex($query)) {
            $query = $this->addLengthForTextIndex($query);
            DB::statement($query);
        }
    }

    private function getDumpArray()
    {
        return preg_split('/;(\n|\r\n)/u', file_get_contents(base_path(self::SQL_PATH)));
    }

    private function isOfCreateTable($query)
    {
        return preg_match('/^CREATE TABLE/u', $query) > 0;
    }

    private function isOfInsert($query)
    {
        return preg_match('/^INSERT INTO/u', $query) > 0;
    }

    private function isOfCreateIndex($query)
    {
        return preg_match('/^CREATE INDEX/u', $query) > 0;
    }

    private function setTableColumn($query)
    {
        preg_match('/^CREATE TABLE (.+) \((.+)\)/msu', $query, $match);
        $column = preg_split('/,\s*/', $match[2]);
        $this->table = trim($match[1]);
        $this->column = array_map(function ($value) {
            preg_match('/^(\w+) [\w ]+$/u', $value, $match);
            return $match[1];
        }, $column);
    }

    private function addLengthForTextIndex($query)
    {
        preg_match('/^(.+)\((.+)\)$/', $query, $match);
        $columns = $match[2];
        $columns = preg_split('/\s*,\s*/', $columns);
        $columns = array_map(function ($column) {
            if (in_array($column, self::TEXT_LOOKUP)) {
                return $column . '(' . self::TEXT_INDEX_LENGTH . ')';
            }
            return $column;
        }, $columns);
        $columns = implode(',', $columns);
        return $match[1] . '(' . $columns . ')';
    }

    private function addInsertValues($query)
    {
        preg_match('/^INSERT INTO.+VALUES\((.+)\)/msu', $query, $match);
        $this->insertValues[] = preg_split('/\s*,\s*/', $match[1]);
    }

    private function processBulkInsert($index)
    {
        if (! $this->isOfChunkLimit($index)) {
            return false;
        }
        echo "chunk {$this->insertCount} processing...\n";
        $this->insertCount++;
        $this->bulkInsert();
        $this->insertValues = [];
    }

    private function bulkInsert()
    {
        $columnString = $this->convertArrayToString($this->column);
        $insertValuesString = [];
        foreach ($this->insertValues as $value) {
            $insertValuesString[] = $this->convertArrayToString($value);
        }
        $insertValuesString = implode(',', $insertValuesString);
        $sql = "insert into $this->table $columnString values $insertValuesString";
        DB::insert($sql);
    }

    private function convertArrayToString($array)
    {
        return '(' . implode(',', $array) . ')';
    }

    private function isOfChunkLimit($index)
    {
        return sizeof($this->insertValues) == self::CHUNK_SIZE
            or ! $this->isOfInsert($this->dump[$index + 1]);
    }
}
