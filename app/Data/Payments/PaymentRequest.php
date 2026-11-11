<?php

namespace App\Data\Payments;

final readonly class PaymentRequest
{
    public function __construct(
        public string $reference,
        public int $amountMinor,
        public string $currency,
        public string $description,
        public array $metadata = [],
    ) {
        if ($this->amountMinor <= 0) {
            throw new \InvalidArgumentException('O valor do pagamento deve ser superior a zero.');
        }

        if (strlen($this->currency) !== 3) {
            throw new \InvalidArgumentException('A moeda deve usar um código de três letras.');
        }
    }
}
