<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        return ['name' => $user->name];
    }
}
