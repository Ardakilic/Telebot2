<?php

namespace App\Repositories;

use App\Models\Bot;

class EloquentBotRepository extends Repository implements BotRepositoryInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create($name, $token)
    {
        return Bot::firstOrCreate([
            'name' => trim($name),
            'token' => trim($token),
        ]);
    }

    public function existsWithName($name)
    {
        $name = trim($name);

        return Bot::where('name', $name)->first() ? true : false;
    }

    public function findUsingId($id)
    {
        $bot = Bot::with('responses')->find($id);
        if (!$bot) {
            return false;
        }

        //I want the keys of the responses to be the patterns
        $bot->responses = $bot->responses->keyBy('pattern');

        return $bot->toArray();
    }
}
