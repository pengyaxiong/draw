<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_problem';

    public function category()
    {
        return $this->belongsTo(ProblemCategory::class);
    }

}
