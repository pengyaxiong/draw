<?php

namespace App\Models;

use App\Models\Shop\Address;
use App\Models\Shop\Order;
use App\Models\Shop\Withdraw;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_customer';


    public function parent()
    {
        return $this->belongsTo(get_class($this));
    }


    public function children()
    {
        return $this->hasMany(get_class($this), 'parent_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function withdraws()
    {
        return $this->hasMany(Withdraw::class);
    }
}
