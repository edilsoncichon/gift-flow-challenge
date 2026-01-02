<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookJob;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcessWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        ProcessWebhookJob::dispatch($request->all());

        return response()->json(['message' => 'Webhook received'], Response::HTTP_OK);
    }
}
