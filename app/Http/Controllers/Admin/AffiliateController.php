<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateWithdrawal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        Inertia::setRootView('layouts.admin-inertia');

        $status = $request->query('status');
        $tab = $request->query('tab', 'affiliates');

        $affiliates = Affiliate::with('user')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->withCount('commissions')
            ->latest()
            ->paginate(20)
            ->through(fn ($a) => [
                'id' => $a->id,
                'user_name' => $a->user->name,
                'user_email' => $a->user->email,
                'code' => $a->code,
                'effective_rate' => $a->effective_rate,
                'commissions_count' => $a->commissions_count,
                'total_earned' => $a->total_earned,
                'total_paid' => $a->total_paid,
                'pending_balance' => $a->pending_balance,
                'status' => $a->status,
                'created_at' => $a->created_at->format('d/m/Y'),
            ]);

        $withdrawals = AffiliateWithdrawal::with('affiliate.user')
            ->latest()
            ->paginate(20, ['*'], 'withdrawals_page')
            ->through(fn ($w) => [
                'id' => $w->id,
                'affiliate_name' => $w->affiliate->user->name,
                'affiliate_code' => $w->affiliate->code,
                'amount' => $w->amount,
                'payment_method' => $w->payment_method,
                'status' => $w->status,
                'admin_note' => $w->admin_note,
                'created_at' => $w->created_at->format('d/m/Y H:i'),
                'paid_at' => $w->paid_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Admin/Affiliates/Index', [
            'affiliates' => $affiliates,
            'withdrawals' => $withdrawals,
            'filters' => ['status' => $status, 'tab' => $tab],
        ]);
    }

    public function commissions(Affiliate $affiliate)
    {
        $commissions = $affiliate->commissions()
            ->with('order')
            ->latest()
            ->paginate(20)
            ->through(fn ($c) => [
                'id' => $c->id,
                'order_number' => $c->order->order_number,
                'order_total' => $c->order_total,
                'commission_rate' => $c->commission_rate,
                'commission_amount' => $c->commission_amount,
                'status' => $c->status,
                'created_at' => $c->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json($commissions);
    }

    public function approve(Affiliate $affiliate)
    {
        $affiliate->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Affilié approuvé.');
    }

    public function suspend(Affiliate $affiliate)
    {
        $affiliate->update(['status' => 'suspended']);

        return back()->with('success', 'Affilié suspendu.');
    }

    public function payWithdrawal(Request $request, AffiliateWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Ce retrait a déjà été traité.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:255',
        ]);

        $withdrawal->update([
            'status' => 'paid',
            'admin_note' => $request->admin_note,
            'paid_at' => now(),
        ]);

        // Lier les commissions confirmées au retrait
        $affiliate = $withdrawal->affiliate;
        $remaining = $withdrawal->amount;

        $commissions = AffiliateCommission::where('affiliate_id', $affiliate->id)
            ->where('status', 'confirmed')
            ->whereNull('withdrawal_id')
            ->oldest()
            ->get();

        foreach ($commissions as $commission) {
            if ($remaining <= 0) {
                break;
            }

            $commission->update([
                'withdrawal_id' => $withdrawal->id,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            $remaining -= $commission->commission_amount;
        }

        $affiliate->increment('total_paid', $withdrawal->amount);

        return back()->with('success', 'Retrait marqué comme payé.');
    }

    public function rejectWithdrawal(Request $request, AffiliateWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Ce retrait a déjà été traité.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:255',
        ]);

        $withdrawal->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        return back()->with('success', 'Retrait rejeté.');
    }
}
