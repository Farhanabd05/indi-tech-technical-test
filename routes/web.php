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
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth', 'role:administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('labels', LabelController::class);
    Route::resource('priorities', PriorityController::class);
    Route::resource('sla-rules', SlaRuleController::class);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth'])->name('dashboard');
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/admin', AdminDashboardController::class)
        ->middleware('role:administrator')
        ->name('admin');
        
    Route::get('/supervisor', SupervisorDashboardController::class)
        ->middleware('role:supervisor')
        ->name('supervisor');
        
    Route::get('/agent', AgentDashboardController::class)
        ->middleware('role:agent')
        ->name('agent');
        
    Route::get('/customer', CustomerDashboardController::class)
        ->middleware('role:customer')
        ->name('customer');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::get('/tickets/export', [TicketController::class, 'export'])
        ->middleware(['role:administrator,supervisor'])
        ->name('tickets.export');
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
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    // hanya admin dan supervisor yang bisa akses
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->middleware(['role:administrator,supervisor'])
        ->name('activity_logs.index');
    Route::get('/notifications/{id}/read', function ($id) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        return redirect()->route('tickets.show', $notification->data['ticket_id']);
    })->name('notifications.read');
    Route::post('/tickets/{ticket}/attachments', [AttachmentController::class, 'store'])
        ->middleware('can:upload,ticket')
        ->name('tickets.attachments.store');
});


require __DIR__.'/auth.php';
