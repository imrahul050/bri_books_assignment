<?php

namespace App\Repositories;

use App\Models\User;




class UserAuthRepository extends BaseRepository
{

    public function register($request)
    {
        $user = User::create($request->only('name', 'email', 'password'));
        return $user;
    }
}
