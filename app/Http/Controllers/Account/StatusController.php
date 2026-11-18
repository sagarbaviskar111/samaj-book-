<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class StatusController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->isActive() || $user->isAdmin()) {
            return redirect()->to(Route::has('home') ? route('home') : '/');
        }

        return view('account.status', ['user' => $user]);
    }
}
