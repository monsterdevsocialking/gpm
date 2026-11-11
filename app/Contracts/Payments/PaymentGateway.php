<?php

namespace App\Contracts\Payments;

use App\Data\Payments\PaymentRequest;
use App\Data\Payments\PaymentResult;

interface PaymentGateway
{
    /**
     * Start a payment with the external provider.
     */
    public function createPayment(PaymentRequest $request): PaymentResult;

    /**
     * Verify and normalize a provider webhook.
     *
     * Implementations must validate signatures and reject invalid payloads.
     */
    public function verifyWebhook(string $payload, array $headers): array;
}
