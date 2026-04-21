<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CollectionCardController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/', 'auth.login');
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/collection', CollectionController::class)->name('collection.index');
    Route::get('/collection/create', [CollectionCardController::class, 'create'])->name('collection.create');
    Route::post('/collection', [CollectionCardController::class, 'store'])->name('collection.store');
    Route::get('/collection/{userCard}/edit', [CollectionCardController::class, 'edit'])->name('collection.edit');
    Route::put('/collection/{userCard}', [CollectionCardController::class, 'update'])->name('collection.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
