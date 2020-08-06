<?php

namespace App\Models\Shop;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'activity_log';

    public function customer()
    {
        return $this->belongsTo(Customer::class,'causer_id');
    }

    public function getPropertiesAttribute($properties)
    {
        return json_decode($properties, true);
    }

}
