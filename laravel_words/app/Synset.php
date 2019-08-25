<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Synset extends Model
{
    protected $table = 'synset_def';
    public $incrementing = true;
    // protected $primaryKey = ['synset', 'lang'];
    protected $primaryKey = 'synsetid';
}
