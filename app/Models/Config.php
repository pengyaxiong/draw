<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_config';

    public $timestamps = false;

    public function getRuleAttribute($rule)
    {
        return array_values(json_decode($rule, true) ?: []);
    }

    public function setRuleAttribute($rule)
    {
        $this->attributes['rule'] = json_encode(array_values($rule));
    }

    public function getCoinSearchAttribute($search)
    {
        return array_values(json_decode($search, true) ?: []);
    }

    public function setCoinSearchAttribute($search)
    {
        $this->attributes['coin_search'] = json_encode(array_values($search));
    }
}
