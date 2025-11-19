<?php

namespace Shibin\Aramex\DTO;

class Shipment
{
    public function __construct(
        public Address $shipper,
        public Address $consignee,
        public int $shipping_date_time,
        public int $due_date,
        public string $pickup_location,
        public float $weight,
        public int $number_of_pieces = 1,
        public string $description = 'General Goods',
        public ?string $payment_type = null,
        public ?float $collect_amount = 0.0,
        public ?float $cash_on_delivery_amount = 0.0,
        public ?string $comments = '',
    ) {}
}