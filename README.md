# Network Address Types for PHP

This library provides types to handle the most common network addresses.
The following address types are supported:

 - MAC addresses in the following forms:
    - Colon separated (`00:00:00:00:00:00`)
    - Dash separated (`00-00-00-00-00-00`)
    - Without separator (`000000000000`)
 - IPv4 (ex: `127.0.0.1`)
 - IPv6 (ex: `::1`)
 - CIDR addresses for IPv4 and IPv6 (ex: `129.0.0.1/12`, `ff80:e::/64`)

## Installation

Installation can be performed with composer:

```bash
composer require luka/network-address-types
```

### Requirements

 - PHP >= 7.4
 - `ext-gmp` for handling IPv6 calculations
 - `ext-json` as it supports json_encode by implementing `JSONSerializable`
 
# Getting started

```php
use LUKA\Network\NetworkAddress;

$address = NetworkAddress::fromString('127.0.0.1');
assert($address instanceof \LUKA\Network\IPv4\IPv4Address);
$address = NetworkAddress::fromString('127.0.0.1/8');
assert($address instanceof \LUKA\Network\IPv4\CIDRv4Address);
$address = NetworkAddress::fromString('::1');
assert($address instanceof \LUKA\Network\IPv6\IPv6Address);
$address = NetworkAddress::fromString('ff80::1/64');
assert($address instanceof \LUKA\Network\IPv6\CIDRv6Address);
$address = NetworkAddress::fromString('84:34:ff:ff:ff:ff');
assert($address instanceof \LUKA\Network\MACAddress);
```

# Serialization

All types can be constructed from strings and can therefore be converted
to strings as well with the `toString()` method.  

```php
use LUKA\Network\NetworkAddress;

assert('::1' === NetworkAddress::fromString('::1')->toString());
```

When converting addresses to strings, they will be normalized for the corresponding type:

```php
use LUKA\Network\NetworkAddress;

assert('::1' === NetworkAddress::fromString('0:0:0:0::1')->toString());
assert('00:00:00:00:00:00' === NetworkAddress::fromString('00-00-00-00-00-00')->toString());
```

## JSON

All address types implement the `JSONSerializable` interface, and can therefore be used with 
`json_encode()` directly.

## Binary

IP and MAC addresses can also be converted to the corresponding byte sequence. (for example to storing them in
a `BINARY` database field).

They can also be constructed from this byte sequence with the static `fromByteString()` method of the corresponding
class.

# Address Comparison

## Compare equality

Each address implements an `equals()` method to compare it to other
network addresses.

Addresses are considered equal when they are from the same type and contain
the same value:

```php
use LUKA\Network\NetworkAddress;

assert(
    true === NetworkAddress::fromString('::1')
        ->equals(NetworkAddress::fromString('::1'))
);

// Value mismatch:
assert(
    false === NetworkAddress::fromString('::1')
        ->equals(NetworkAddress::fromString('::2'))
);

// Type mismatch (different IP version):
assert(
    false === NetworkAddress::fromString('::1')
        ->equals(NetworkAddress::fromString('127.0.0.1'))
);

// Type mismatch (cidr vs non-cidr)
assert(
    false === NetworkAddress::fromString('192.168.0.5')
        ->equals(NetworkAddress::fromString('192.168.0.5/24'))
);
```

## Comparing addresses to networks

CIDR addresses allow to obtain the corresponding network to the denoted address.
With this network you can check if an IP address is within this network.

```php
use LUKA\Network\NetworkAddress;

$cidr = NetworkAddress::fromString('192.168.0.7/8');
$network = $cidr->toNetwork();

assert(true === $network->containsAddress(NetworkAddress::fromString('192.168.0.1')));
assert(true === $network->containsAddress(NetworkAddress::fromString('192.45.0.2')));
assert(false === $network->containsAddress(NetworkAddress::fromString('127.10.0.1')));
assert(false === $network->containsAddress(NetworkAddress::fromString('ff80::5')));
```

This will work for IPv6 as well:

```php
use LUKA\Network\NetworkAddress;

$cidr = NetworkAddress::fromString('ff80::/64');
$network = $cidr->toNetwork();

assert(true === $network->containsAddress(NetworkAddress::fromString('ff80::1')));
assert(true === $network->containsAddress(NetworkAddress::fromString('ff80::10:e5:7')));
assert(false === $network->containsAddress(NetworkAddress::fromString('ff80:e::1')));
assert(false === $network->containsAddress(NetworkAddress::fromString('127.0.0.1')));
```
