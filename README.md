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

---

If you want a **logo, badges, or screenshots**, tell me and I will generate them.

