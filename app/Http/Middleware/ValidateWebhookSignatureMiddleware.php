<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateWebhookSignatureMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-GiftFlow-Signature');

        if (! $signature) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $request->getContent(),
            config('giftflow.webhook_secret')
        );

        if (! hash_equals($expectedSignature, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
