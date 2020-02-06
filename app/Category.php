<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Categories model
 *
 * Class Category
 * @package App
 */
class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'category_name',
        'parent_id',
    ];
}
