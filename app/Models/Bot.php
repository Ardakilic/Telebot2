<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'token',
    ];

    public function responses()
    {
        return $this->hasMany('App\Models\Response', 'bot_id');
    }
}
