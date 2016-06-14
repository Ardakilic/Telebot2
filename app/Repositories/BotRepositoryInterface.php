<?php

namespace App\Repositories;

interface BotRepositoryInterface
{
    public function create($title, $token);

    public function existsWithName($name);

    public function findUsingId($id);
}
