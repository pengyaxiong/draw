<?php

namespace App\Models\Shop;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_shop_order';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function address()
    {
        return $this->hasOne(OrderAddress::class);
    }

    public function order_products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function order_comments()
    {
        return $this->hasMany(Comment::class);
    }

}
