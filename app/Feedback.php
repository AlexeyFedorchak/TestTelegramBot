<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * get feedbacks
 *
 * Class Feedback
 * @package App
 */
class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'feedback',
        'user_name',
        'user_id',
    ];
}
