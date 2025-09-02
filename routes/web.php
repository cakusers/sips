<?php

use App\Http\Controllers\Invoice;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/', '/admin');

Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::get('/admin/transactions/{id}/invoice', [Invoice::class, 'show'])
    ->name('print-invoice')
    ->middleware('auth');
