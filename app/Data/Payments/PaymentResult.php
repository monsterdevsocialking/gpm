<?php

namespace App\Data\Payments;

final readonly class PaymentResult
{
    public function __construct(
        public string $providerReference,
        public string $status,
        public ?string $redirectUrl = null,
        public array $metadata = [],
    ) {
    }
}
