<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $recentSigned = Contract::where('status', 'firmado')
            ->whereNotNull('sealed_at')
            ->latest('sealed_at')
            ->take(3)
            ->get(['reference', 'title', 'sealed_at']);

        return view('landing', [
            'countries' => Contract::LAW_COUNTRIES,
            'recentSigned' => $recentSigned,
        ]);
    }
}
