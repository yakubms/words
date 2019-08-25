<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    public $table = 'word';
    public $incrementing = true;
    protected $primaryKey = 'wordid';

    public function senses()
    {
        return $this->hasMany(Sense::class, 'wordid');
    }
}
