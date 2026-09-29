<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use App\Models\AffiliateClick;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAffiliateRef
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            $code = $request->query('ref');
            $affiliate = Affiliate::active()->where('code', $code)->first();

            if ($affiliate) {
                $response = $next($request);

                cookie()->queue(cookie('affiliate_ref', $code, 60 * 24 * 30, '/', null, false, true));

                AffiliateClick::create([
                    'affiliate_id' => $affiliate->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $response;
            }
        }

        return $next($request);
    }
}
