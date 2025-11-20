# Laravel Aramex SDK 🚀

A clean, modern, Laravel SDK for **Aramex Shipping Services**.  
Designed to be simple enough for junior developers and powerful enough for production.

<p align="center">
  <!-- <img src="https://img.shields.io/badge/Laravel-10.x%20%7C%2011.x-red?style=for-the-badge" /> -->
  <img src="https://img.shields.io/badge/PHP-8.1+-blue?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Status-Stable-brightgreen?style=for-the-badge" />
  <!-- <img src="https://img.shields.io/badge/Aramex-API-orange?style=for-the-badge" /> -->
</p>

---

## 📑 Table of Contents

- [Features](#-features)
- [Installation](#-installation)
- [Configuration](#%EF%B8%8F-configuration)
- [DTO Reference](#-dto-reference)
- [Usage Examples](#-usage-examples)
  - [Fetch Locations](#-fetch-locations)
  - [Calculate Shipping Rates](#-calculate-shipping-rates)
  - [Create a Shipment](#-create-a-shipment)
  - [Print Labels](#-print-labels)
  - [Track Shipments](#-track-shipments)
  - [Schedule a Pickup](#-schedule-a-pickup)

---

## 🚀 Features

- ✅ Create Shipments
- ✅ Track Shipments
- ✅ Calculate Rates
- ✅ Create Pickups
- ✅ Validate Addresses
- ✅ Fetch Countries & Cities
- ✅ **Fully Typed DTOs** (Data Transfer Objects)
- ✅ Automatic **TEST / LIVE** WSDL switching
- ✅ Laravel auto-discovery

---

## 📦 Installation

Install the package via Composer:

```bash
composer require shibin/aramex-laravel
```

---

## ⚙️ Configuration

### 1. Publish Config
Publish the configuration file to customize settings if necessary:

```bash
php artisan vendor:publish --tag=aramex-config
```

### 2. Environment Setup
Add the following variables to your `.env` file. 

**Note:** Ensure `ARAMEX_ACCOUNT_COUNTRY` matches the country of your account issuance (e.g., JO for Jordan, IN for India).

```dotenv
# Environment: TEST or LIVE
ARAMEX_ENV=TEST

# Credentials
ARAMEX_TEST_ACCOUNT_NUMBER=20016
ARAMEX_TEST_USERNAME=testingapi@aramex.com
ARAMEX_TEST_PASSWORD=R123456789$r
ARAMEX_TEST_ACCOUNT_PIN=331421
ARAMEX_TEST_ACCOUNT_ENTITY=AMM
ARAMEX_TEST_ACCOUNT_COUNTRY=JO
```

---

## 🧱 DTO Reference

This package uses Data Transfer Objects (DTOs) to ensure data integrity.

### Address DTO
Used for Origin, Destination, Shipper, and Consignee.

```php
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
```

### Shipment DTO
Defines the details of the package being sent.

```php
use Shibin\Aramex\DTO\Shipment;

$shipment = new Shipment(
    shipper: $shipperAddress,     // Address Object
    consignee: $consigneeAddress, // Address Object
    shipping_date_time: time() + 3600,
    due_date: time() + 86400,
    pickup_location: 'Reception',
    weight: 2.5,
    number_of_pieces: 1,
    description: 'Electronics',
    payment_type: 'P', // P = Prepaid, C = Collect
);
```

---

## 💡 Usage Examples

### 🌍 Fetch Locations

```php
use Shibin\Aramex\Facades\Aramex;

// Get all countries
$countries = Aramex::location()->countries();

// Get cities within a country
$cities = Aramex::location()->cities('SA'); // Saudi Arabia
```

### 💰 Calculate Shipping Rates

```php
use Shibin\Aramex\Facades\Aramex;
use Shibin\Aramex\DTO\Address;
use Shibin\Aramex\DTO\RateRequest;

// 1. Define Origin & Destination
$origin = new Address(line1: 'Street', city: 'Amman', country_code: 'JO');
$destination = new Address(line1: 'Street', city: 'Jeddah', country_code: 'SA');

// 2. Create Request
$request = new RateRequest(
    origin: $origin,
    destination: $destination,
    weight: 5.0,
    pieces: 1,
    currency: 'USD'
);

// 3. Calculate
$response = Aramex::rates()->calculate($request);
```

### 📦 Create a Shipment

```php
use Shibin\Aramex\Facades\Aramex;
use Shibin\Aramex\DTO\Address;
use Shibin\Aramex\DTO\Shipment;

$shipper = new Address(
    line1: 'Gardens St', city: 'Amman', country_code: 'JO',
    name: 'Shipper Name', email: 'shipper@test.com', phone: '0790000000'
);

$consignee = new Address(
    line1: 'Fahad Rd', city: 'Riyadh', country_code: 'SA',
    name: 'Receiver Name', email: 'receiver@test.com', phone: '0500000000'
);

$shipment = new Shipment(
    shipper: $shipper,
    consignee: $consignee,
    shipping_date_time: time() + 3600,
    due_date: time() + 86400,
    comments: 'Handle with care',
    pickup_location: 'Reception',
    weight: 2.5,
    number_of_pieces: 1,
    description: 'Electronics',
    payment_type: 'P'
);

$response = Aramex::shipments()->create($shipment);

// Access the Waybill Number
$waybill = $response->Shipments->ProcessedShipment->ID ?? null;
```

### 🖨 Print Labels

You can download the shipment label as a PDF.

```php
$shipmentNumber = "37133804481"; // The ID from the create shipment response

$response = Aramex::shipments()->printLabel($shipmentNumber);

if (isset($response->ShipmentLabel->LabelFileContents)) {
    $pdfContent = $response->ShipmentLabel->LabelFileContents;
    
    // Save to storage
    Storage::disk('public')->put("label_{$shipmentNumber}.pdf", $pdfContent);
}
```

### 🔍 Track Shipments

```php
$waybill = "37133804481";
$response = Aramex::tracking()->track([$waybill]);
```

### 🚚 Schedule a Pickup

```php
use Shibin\Aramex\DTO\Pickup;

$pickup = new Pickup();
$pickup->address = $addressObject;
$pickup->pickup_location = 'Reception';
$pickup->pickup_date = strtotime('+1 day');
$pickup->ready_time = strtotime('10:00', $pickup->pickup_date);
$pickup->last_pickup_time = strtotime('15:00', $pickup->pickup_date);
$pickup->closing_time = strtotime('17:00', $pickup->pickup_date);
$pickup->weight = 5;
$pickup->volume = 2;

$response = Aramex::pickup()->create($pickup);
```

### ✅ Validate Address

```php
$isValid = Aramex::location()->validateAddress($addressObject);
```