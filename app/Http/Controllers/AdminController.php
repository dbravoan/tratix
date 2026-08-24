<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Signature;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'contracts' => Contract::count(),
            'signed' => Contract::where('status', 'firmado')->count(),
            'signatures' => Signature::count(),
            'pro_users' => User::where('plan', 'pro')->count(),
        ];

        $byStatus = Contract::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentContracts = Contract::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.index', compact('stats', 'byStatus', 'recentContracts'));
    }
}
