# Laravel Aramex SDK (Modern + Clean)

A clean, modern, Laravel SDK for **Aramex Shipping Services**.  
Designed to be simple enough for junior developers and powerful enough for production.

## 🚀 Features

- Create Shipments  
- Track Shipments  
- Calculate Rates  
- Create Pickups  
- Fetch Countries & Cities  
- Validate Addresses  
- Fully typed DTOs  
- Automatic TEST / LIVE WSDL switching  
- Laravel auto-discovery  
- PHP 8+  

---

## 📦 Installation

```bash
composer require shibin/aramex-laravel

---

## ⚙️ Publish Configuration

```bash
php artisan vendor:publish --tag=aramex-config

---

## Environment Setup
# TEST or LIVE
ARAMEX_ENV=TEST

# Test Credentials
ARAMEX_TEST_ACCOUNT_NUMBER=20016
ARAMEX_TEST_USERNAME=testingapi@aramex.com
ARAMEX_TEST_PASSWORD=R123456789$r
ARAMEX_TEST_ACCOUNT_PIN=331421
ARAMEX_TEST_ACCOUNT_ENTITY=AMM
ARAMEX_TEST_ACCOUNT_COUNTRY=JO

## DTO Examples
```bash
use Shibin\Aramex\DTO\Address;

$address = new Address(
    line1: 'Gardens Street',
    city: 'Amman',
    country_code: 'JO',
    name: 'John Doe',
    email: 'john@example.com',
    phone: '0790000000',
    cell_phone: '0790000000',
);
use Shibin\Aramex\DTO\Shipment;

$shipment = new Shipment(
    shipper: $shipper,
    consignee: $consignee,
    shipping_date_time: time() + 3600,
    due_date: time() + 86400,
    pickup_location: 'Reception',
    weight: 2.5,
    number_of_pieces: 1,
    description: 'Electronics',
    payment_type: 'P',
);

use Shibin\Aramex\DTO\Pickup;

$pickup = new Pickup();
$pickup->address = $address;
$pickup->pickup_location = 'Warehouse';
$pickup->pickup_date = '2025-11-21';
$pickup->ready_time = '10:00';
$pickup->last_pickup_time = '17:00';
$pickup->closing_time = '17:30';
$pickup->weight = 5;
$pickup->volume = 2;

use Shibin\Aramex\DTO\RateRequest;

$rateRequest = new RateRequest(
    origin: $origin,
    destination: $destination,
    weight: 2.5,
    currency: 'USD',
    pieces: 1
);

## Create Shipment

$response = Aramex::shipments()->create($shipment);

$waybill = $response->Shipments->ProcessedShipment->ID;

## Track Shipment
$response = Aramex::tracking()->track([$waybill]);

##Calculate Rate
$response = Aramex::rate()->calculate($rateRequest);

## 📦 Create Pickup
$response = Aramex::pickup()->create($pickup);
## 🌍 Fetch Countries & Cities
$countries = Aramex::location()->countries();
$cities = Aramex::location()->cities('JO', 'Amman');

## 🏠 Validate Address
$response = Aramex::location()->validateAddress($address);

## Using
<?php

use Illuminate\Support\Facades\Route;
use Shibin\Aramex\Facades\Aramex;
use Shibin\Aramex\DTO\Address;
use Shibin\Aramex\DTO\RateRequest;
use Shibin\Aramex\DTO\Shipment;
use Shibin\Aramex\DTO\Pickup;


Route::get('/test-cities', function () {
    // return Aramex::location()->countries();

    return Aramex::location()->cities('SA');
});

Route::get('/test-rate', function () {
    try {
        // 1. Define Origin (Must match your Account Country for testing mostly)
        $origin = new Address(
            line1: 'Gardens Street',
            city: 'Amman',
            country_code: 'JO'
        );

        // 2. Define Destination
        $destination = new Address(
            line1: 'King Fahad Road',
            city: 'Jeddah',
            country_code: 'SA'
        );

        // 3. Create Rate Request Object
        $request = new RateRequest(
            origin: $origin,
            destination: $destination,
            weight: 5.0, // 5 KG
            pieces: 1,
            currency: 'USD'
        );

        // 4. Call the API
        $response = Aramex::rates()->calculate($request);

        return response()->json($response);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/test-shipment', function () {
    try {
        // 1. Define Shipper (Must be Amman/Jordan for Test Account 20016)
        $shipper = new Address(
            line1: 'Gardens Street',
            city: 'Amman',
            country_code: 'JO',
            name: 'Test Shipper',
            email: 'shipper@example.com',
            phone: '0790000000',
            cell_phone: '0790000000'
        );

        // 2. Define Consignee (Can be anywhere, e.g., Jeddah)
        $consignee = new Address(
            line1: 'King Fahad Road',
            city: 'Jeddah',
            country_code: 'SA',
            name: 'Test Consignee',
            email: 'consignee@example.com',
            phone: '0500000000',
            cell_phone: '0500000000'
        );

        // 3. Create Shipment DTO
        $shipment = new Shipment(
            shipper: $shipper,
            consignee: $consignee,
            shipping_date_time: time() + 3600, // Ship 1 hour from now
            due_date: time() + (24 * 3600),    // Due tomorrow
            comments: 'Handle with care',
            pickup_location: 'Reception',
            weight: 2.5,
            number_of_pieces: 1,
            description: 'Electronics', // Description of goods
            payment_type: 'P' // P = Prepaid, C = Collect
        );

        // 4. Call the API
        $response = Aramex::shipments()->create($shipment);

        $waybill = $response->Shipments->ProcessedShipment->ID ?? null;

        return response()->json([
            "success" => true,
            "waybill" => $waybill,
            "response" => $response
        ]);

        return response()->json($response);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/test-print-label', function () {
    try {
        // REPLACE THIS with a real Shipment Number you created earlier!
        // Example: "31258391232"
        $shipmentNumber = "37133804481";

        // Call the new API (Optionally pass 9729 or 9201 as 2nd argument)
        $response = Aramex::shipments()->printLabel($shipmentNumber);

        // Check if we got a file
        if (isset($response->ShipmentLabel->LabelFileContents)) {

            $binaryData = $response->ShipmentLabel->LabelFileContents;

            // Save it to storage
            $fileName = "reprint_{$shipmentNumber}.pdf";
            // \Storage::disk('public')->put($fileName, $binaryData);

            return response()->json([
                'status' => 'Success',
                'message' => 'Label saved to storage/app/public/' . $fileName,
                'download_link' => asset('storage/' . $fileName)
            ]);
        }

        // Fallback if Aramex sent a URL instead
        return response()->json($response);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/test-tracking/{waybill}', function ($waybill) {
    try {
        // Call tracking API
        $response = Aramex::tracking()->track([$waybill]);

        return response()->json([
            "success" => true,
            "waybill" => $waybill,
            "tracking" => $response
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/test-create-pickup', function () {
    try {
        $address = new Address(
            line1: 'Aramex Building',
            city: 'Amman',
            country_code: 'JO',
            name: 'Test User',
            email: 'test@example.com',
            phone: '0790000000',
            cell_phone: '0790000000'
        );

        $pickupDate = strtotime('+1 day');

        $pickup = new Pickup();
        $pickup->address = $address;
        $pickup->pickup_location = 'Reception';
        $pickup->pickup_date = $pickupDate;
        $pickup->ready_time = strtotime('10:00', $pickupDate);
        $pickup->last_pickup_time = strtotime('15:00', $pickupDate);
        $pickup->closing_time = strtotime('17:00', $pickupDate);
        $pickup->weight = 5;
        $pickup->volume = 2;

        $response = Aramex::pickup()->create($pickup);

        return response()->json($response);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
});

---

If you want a **logo, badges, or screenshots**, tell me and I will generate them.



