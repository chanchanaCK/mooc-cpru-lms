<?php

namespace App\Services\Payment;

/** Outcome of a gateway charge attempt. */
class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $reference,
        public readonly ?string $method = null,
        public readonly ?string $failureMessage = null,
        public readonly array $meta = [],
    ) {
    }

    public static function success(string $reference, ?string $method = null, array $meta = []): self
    {
        return new self(true, $reference, $method, null, $meta);
    }

    public static function failure(string $reference, string $message, ?string $method = null): self
    {
        return new self(false, $reference, $method, $message);
    }
}
