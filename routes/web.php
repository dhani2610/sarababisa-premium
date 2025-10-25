<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MemberController;

Route::get('/', [MemberController::class, 'index'])->name('members.index');
Route::post('/members', [MemberController::class, 'store'])->name('members.store');
Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
Route::post('/members/{member}/update-expired', [MemberController::class, 'updateExpired'])->name('members.update-expired');
