<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Basket extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'extras' => 'array'
    ];

    function extendLifetime(){
        $date = new \DateTime;
        $date->modify('+7 days');
        $this->updated_at = $date;
        $this->save();
        logger()->channel('info')->info('extended basket lifetime');
    }

    function updateStatus($status){

        $this->status = $status;
        $this->save();
        //logger()->channel('info')->info('basket status updated: '. $status);
    }



}
