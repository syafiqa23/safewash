<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isCustomer()) {
            return redirect()->route('dashboard');
        }

        $loyaltyAccount = $user->loyaltyAccount()->with('transactions')->first();

        $history = LoyaltyTransaction::whereHas('account', fn ($q) => $q->where('customer_id', $user->id))
            ->with('order')
            ->latest()
            ->paginate(20);

        $tiers = [
            ['name' => 'Ocean',  'min' => 0,    'max' => 499,  'color' => 'accent', 'icon' => 'waves'],
            ['name' => 'Sky',    'min' => 500,  'max' => 1499, 'color' => 'green',  'icon' => 'cloud'],
            ['name' => 'Cloud',  'min' => 1500, 'max' => 3999, 'color' => 'purple', 'icon' => 'cloud-snow'],
            ['name' => 'Aurora', 'min' => 4000, 'max' => null, 'color' => 'amber',  'icon' => 'sparkles'],
        ];

        return view('loyalty.index', compact('user', 'loyaltyAccount', 'history', 'tiers'));
    }
}
