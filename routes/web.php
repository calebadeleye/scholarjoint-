<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/verify-email/{id}', function (Request $request, $id) {
    if (! URL::hasValidSignature($request)) {
        abort(401, 'Invalid or expired verification link.');
    }

    $user = \App\Models\User::findOrFail($id);
    $user->email_verified_at = now();
    $user->save();

    return view('auth.verify-success'); // Or return JSON if it's API
})->name('verify.custom');
