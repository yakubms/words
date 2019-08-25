<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sense extends Model
{
    protected $table = 'sense';
    public $incrementing = true;
    protected $primaryKey = 'senseid';

    public function synsets()
    {
        return $this->hasMany(Synset::class, 'synset', 'synset');
    }
}
