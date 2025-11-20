<?php

namespace Shibin\Aramex\DTO;

class Pickup
{
    public Address $address;

    public string $pickup_location;
    public string $pickup_date;
    public string $ready_time;
    public string $last_pickup_time;
    public string $closing_time;

    public float $weight;
    public float $volume;

    // Add this field
    public string $reference1 = ''; 

    public string $status = 'Ready';
}