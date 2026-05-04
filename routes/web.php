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
use App\Http\Controllers\Admin\PriorityController;
use App\Http\Controllers\Admin\SlaRuleController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\AgentDashboardController;
use App\Http\Controllers\Dashboard\CustomerDashboardController;
use App\Http\Controllers\Dashboard\SupervisorDashboardController;

Route::middleware(['auth', 'role:administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('labels', LabelController::class);
});

Route::prefix('admin')->name('admin.')->group(function () {
    // Daftarkan resource route untuk users, roles, categories, labels, priorities, dan sla-rules di sini
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('labels', LabelController::class);

    Route::resource('priorities', PriorityController::class);
    Route::resource('sla-rules', SlaRuleController::class);
    // Daftarkan get route biasa untuk logs
    Route::get('logs', function () {
        return view('admin.logs');
    })->name('logs');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth'])->name('dashboard');
Route::get('/dashboard/admin', AdminDashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard.admin');
Route::get('/dashboard/agent', AgentDashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard.agent');
Route::get('/dashboard/supervisor', SupervisorDashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard.supervisor');
Route::get('/dashboard/customer', CustomerDashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard.customer');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Parameter {ticket} harus sesuai dengan model yang didefinisikan di TicketPolicy
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
        ->middleware('can:view,ticket')
        ->name('tickets.show');
    Route::resource('tickets', TicketController::class)->only(['index', 'store', 'update']);
    Route::match(['post', 'patch'], '/tickets/{ticket}/assign', TicketAssignController::class)
        ->middleware('can:assign,ticket')
        ->name('tickets.assign');
    Route::patch('/tickets/{ticket}/status', [TicketStatusController::class, 'update'])
        ->middleware('can:changeStatus,ticket')
        ->name('tickets.status.update');
    Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store'])
        ->middleware('can:comment,ticket')
        ->name('tickets.comments.store');
});

require __DIR__.'/auth.php';
