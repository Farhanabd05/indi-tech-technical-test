<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ticket\TicketController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LabelController;
use App\Http\Controllers\Ticket\TicketStatusController;
use App\Http\Controllers\Ticket\TicketAssignController;
use \App\Http\Controllers\CommentController;

Route::middleware(['auth', 'role:administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('labels', LabelController::class);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Parameter {ticket} harus sesuai dengan model yang didefinisikan di TicketPolicy
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
        ->middleware('can:view,ticket')
        ->name('tickets.show');
    Route::resource('tickets', TicketController::class)->only(['index', 'store', 'update']);
    Route::post('/tickets/{ticket}/assign', TicketAssignController::class)
        ->middleware('can:assign,ticket')
        ->name('tickets.assign');
    Route::patch('/tickets/{ticket}/status', [TicketStatusController::class, 'update'])
        ->middleware('can:changeStatus,ticket')
        ->name('tickets.changeStatus');
    Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store'])
        ->middleware('can:comment,ticket')
        ->name('comments.store');
});

require __DIR__.'/auth.php';
