<?php

namespace App\Models\Shop;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_shop_addresses';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
