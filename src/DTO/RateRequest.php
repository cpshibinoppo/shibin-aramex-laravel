<?php

namespace Shibin\Aramex\DTO;

class RateRequest
{
    public function __construct(
        public Address $origin,
        public Address $destination,
        public float $weight,
        public string $currency,
        public int $pieces = 1,
        public ?float $length = null,
        public ?float $width = null,
        public ?float $height = null,
    ) {}
}