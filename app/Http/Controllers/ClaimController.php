<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\LaundryOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClaimController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $claims = Claim::with(['order.laundry', 'customer'])
            ->when($user->isCustomer(), fn ($q) => $q->where('customer_id', $user->id))
            ->when($user->isMerchant(), fn ($q) => $q->whereHas('order', fn ($inner) => $inner->whereIn('laundry_id', $user->laundries()->pluck('id'))))
            ->latest()
            ->paginate(15);

        return view('claims.index', compact('claims', 'user'));
    }

    public function create(LaundryOrder $order): View
    {
        $user = Auth::user();
        abort_unless(
            $order->customer_id === $user->id || $order->customer_email === $user->email,
            403
        );
        return view('claims.create', compact('order'));
    }

    public function store(Request $request, LaundryOrder $order): RedirectResponse
    {
        $user = Auth::user();

        abort_unless(
            $order->customer_id === $user->id || $order->customer_email === $user->email,
            403
        );

        $data = $request->validate([
            'claimant_name'    => ['required', 'string', 'max:255'],
            'claimant_contact' => ['required', 'string', 'max:255'],
            'claim_type'       => ['required', 'in:hilang,rusak,tertukar'],
            'item_name'        => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:2000'],
            'loss_amount'      => ['nullable', 'numeric', 'min:0'],
            'photo'            => ['nullable', 'image', 'max:4096'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('claims', 'public');
        }

        Claim::create([
            'laundry_order_id' => $order->id,
            'customer_id'      => Auth::id(),
            'claimant_name'    => $data['claimant_name'],
            'claimant_contact' => $data['claimant_contact'],
            'claim_type'       => $data['claim_type'],
            'item_name'        => $data['item_name'],
            'description'      => $data['description'],
            'photo_path'       => $photoPath,
            'loss_amount'      => $data['loss_amount'] ?? 0,
            'status'           => 'submitted',
            'submitted_at'     => now(),
        ]);

        $order->update(['claim_status' => 'submitted']);

        return redirect()->route('claims.index')->with('success', 'Klaim berhasil dikirim. Kami akan meninjau dalam 1×24 jam.');
    }
}
