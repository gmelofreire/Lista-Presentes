<?php

use App\Http\Controllers\Api\UsernameController;
use Illuminate\Support\Facades\Route;

Route::get('/check-username', [UsernameController::class, 'checkUsername']);
