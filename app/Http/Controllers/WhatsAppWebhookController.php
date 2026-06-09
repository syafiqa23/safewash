<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request, WhatsAppGatewayService $whatsAppGateway): Response
    {
        $challenge = $whatsAppGateway->verifyWebhook(
            (string) ($request->query('hub.mode') ?? $request->query('hub_mode')),
            (string) ($request->query('hub.verify_token') ?? $request->query('hub_verify_token')),
            (string) ($request->query('hub.challenge') ?? $request->query('hub_challenge'))
        );

        abort_unless($challenge !== null, 403);

        return response($challenge, 200);
    }

    public function receive(Request $request, WhatsAppGatewayService $whatsAppGateway): Response
    {
        $whatsAppGateway->handleWebhookPayload($request->all());

        return response('EVENT_RECEIVED', 200);
    }
}
