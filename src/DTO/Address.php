<?php

namespace Shibin\Aramex\DTO;

class Address
{
    public function __construct(
        public string $line1,
        public string $city,
        public string $country_code,

        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $cell_phone = null,
        public ?string $line2 = null,
        public ?string $line3 = null,
        public ?string $zip_code = null,
    ) {}
}