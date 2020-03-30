<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    public function __construct() {

    }

    public function getCurrentUser() {
        if(Auth::check())
        {
            return json_encode(Auth::user());
        } else {
            return redirect('/login');
        }
    }
}
