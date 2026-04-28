<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class EmailVerifiedCheckController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect('/login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('verification.pending');
    }
}
