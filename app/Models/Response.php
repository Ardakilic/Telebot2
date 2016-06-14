<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'bot_id', 'command', 'response_type', 'as_quote', 'preview_links_if_any',
    ];

    public function bot()
    {
        return $this->hasMany('App\Models\Bot', 'bot_id');
    }
}
