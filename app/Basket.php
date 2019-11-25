<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Basket extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'extras' => 'array'
    ];

}
