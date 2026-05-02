<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $role = Auth::user()->role->slug;

        return match ($role) {
            'administrator' => redirect()->route('dashboard.admin'),
            'supervisor' => redirect()->route('dashboard.supervisor'),
            'agent' => redirect()->route('dashboard.agent'),
            'customer' => redirect()->route('dashboard.customer'),
            default => abort(403, 'Unauthorized'),
        };
    }
}
