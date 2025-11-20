# Laravel Aramex SDK 🚀  
A modern, clean, fully-typed Laravel SDK for **Aramex Shipping Services**.  
Built to be **simple for beginners** and **powerful for production**.

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x%20%7C%2011.x-red?style=for-the-badge" />
  <img src="https://img.shields.io/badge/PHP-8.1+-blue?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Status-Stable-brightgreen?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Aramex-API-orange?style=for-the-badge" />
</p>

---

## 📑 Table of Contents  
- [Features](#-features)  
- [Installation](#-installation)  
- [Publish Config](#️-publish-config)  
- [Environment Variables](#-environment-variables)  
- [DTO Examples](#-dto-examples)  
- [Usage Examples](#-usage-examples)  
  - [Create Shipment](#create-shipment)  
  - [Track Shipment](#track-shipment)  
  - [Calculate Rate](#calculate-rate)  
  - [Create Pickup](#create-pickup)  
  - [Fetch Countries & Cities](#fetch-countries--cities)  
  - [Validate Address](#validate-address)  
- [Test Routes](#-test-routes)

---

## 🚀 Features  
- Create Shipments  
- Track Shipments  
- Calculate Rates  
- Create Pickups  
- Validate Addresses  
- Fetch Countries & Cities  
- Fully Typed DTOs  
- Automatic TEST / LIVE WSDL Switching  
- Easy Laravel Integration  
- PHP 8+ Support  

---

## 📦 Installation  

```bash
composer require shibin/aramex-laravel
```

---

## ⚙️ Publish Config  

```bash
php artisan vendor:publish --tag=aramex-config
```

# Test or Live
ARAMEX_ENV=test

# Test Credentials
ARAMEX_TEST_ACCOUNT_NUMBER=20016
ARAMEX_TEST_USERNAME=testingapi@aramex.com
ARAMEX_TEST_PASSWORD=R123456789$r
ARAMEX_TEST_ACCOUNT_PIN=331421
ARAMEX_TEST_ACCOUNT_ENTITY=AMM
ARAMEX_TEST_ACCOUNT_COUNTRY=JO

# DTO Examples

## Address DTO

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
## Shipment DTO

```php
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
```

## Pickup DTO

```php
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
```

## Rate Request DTO

```php
use Shibin\Aramex\DTO\RateRequest;

$rateRequest = new RateRequest(
    origin: $origin,
    destination: $destination,
    weight: 2.5,
    currency: 'USD',
    pieces: 1
);
```
