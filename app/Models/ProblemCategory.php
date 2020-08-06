<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProblemCategory extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_problem_category';


    public function problems()
    {
        return $this->hasMany(Problem::class,'category_id');
    }

}
