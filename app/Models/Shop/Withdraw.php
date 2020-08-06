<?php

namespace App\Models\Shop;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
class Withdraw extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_withdraw';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
