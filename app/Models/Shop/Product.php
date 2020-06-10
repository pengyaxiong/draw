<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_shop_product';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getImagesAttribute($images)
    {
        return array_values(json_decode($images, true) ?: []);
    }

    public function setImagesAttribute($images)
    {
        $this->attributes['images'] = json_encode(array_values($images));
    }

}
