<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateWithdrawal;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AffiliateController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        $data = [
            'affiliate' => null,
            'commissions' => [],
            'commission_rate' => (float) Setting::get('affiliate_commission_rate', 5),
            'min_withdrawal' => (float) Setting::get('affiliate_min_withdrawal', 2000),
        ];

        if ($affiliate) {
            $commissions = $affiliate->commissions()
                ->with('order:id,order_number,total,created_at')
                ->latest()
                ->take(20)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'order_number' => $c->order->order_number,
                    'order_total' => $c->order_total,
                    'commission_rate' => $c->commission_rate,
                    'commission_amount' => $c->commission_amount,
                    'status' => $c->status,
                    'created_at' => $c->created_at->format('d/m/Y'),
                ]);

            $data['affiliate'] = [
                'id' => $affiliate->id,
                'code' => $affiliate->code,
                'status' => $affiliate->status,
                'payment_method' => $affiliate->payment_method,
                'effective_rate' => $affiliate->effective_rate,
                'total_earned' => $affiliate->total_earned,
                'total_paid' => $affiliate->total_paid,
                'pending_balance' => $affiliate->pending_balance,
                'commissions_count' => $affiliate->commissions()->count(),
                'share_url' => url('/?ref=' . $affiliate->code),
            ];
            $data['commissions'] = $commissions;
        }

        return Inertia::render('Account/Affiliation', $data);
    }

    public function apply(Request $request)
    {
        $user = auth()->user();

        if (Affiliate::where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Vous avez déjà une demande d\'affiliation.');
        }

        $request->validate([
            'payment_method' => 'required|string|max:255',
        ]);

        Affiliate::create([
            'user_id' => $user->id,
            'code' => Affiliate::generateCode($user->name),
            'payment_method' => $request->payment_method,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Demande envoyée ! L\'équipe vous contacte sous 48h.');
    }

    public function updatePayment(Request $request)
    {
        $affiliate = Affiliate::where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'payment_method' => 'required|string|max:255',
        ]);

        $affiliate->update(['payment_method' => $request->payment_method]);

        return back()->with('success', 'Moyen de paiement mis à jour.');
    }

    public function withdraw(Request $request)
    {
        $affiliate = Affiliate::where('user_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();

        $minWithdrawal = (float) Setting::get('affiliate_min_withdrawal', 2000);
        $confirmedBalance = $affiliate->commissions()
            ->where('status', 'confirmed')
            ->whereNull('withdrawal_id')
            ->sum('commission_amount');

        if ($confirmedBalance < $minWithdrawal) {
            return back()->with('error', 'Solde confirmé insuffisant (minimum ' . number_format($minWithdrawal, 0, ',', ' ') . ' F CFA).');
        }

        AffiliateWithdrawal::create([
            'affiliate_id' => $affiliate->id,
            'amount' => $confirmedBalance,
            'payment_method' => $affiliate->payment_method,
        ]);

        return back()->with('success', 'Demande de retrait de ' . number_format($confirmedBalance, 0, ',', ' ') . ' F CFA envoyée.');
    }

    public function validateCode(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            return response()->json(['valid' => false]);
        }

        $exists = Affiliate::active()->where('code', $code)->exists();

        return response()->json(['valid' => $exists]);
    }
}
