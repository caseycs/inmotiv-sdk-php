# InMotiv RDC SOAP PHP client

[![Build Status](https://travis-ci.org/caseycs/inmotiv-sdk-php.svg?branch=master)](https://travis-ci.org/caseycs/inmotiv-sdk-php)

InMotiv (https://www.rdc.nl/) is one of the official RDW partners. It provides SOAP endpoints to access RDW database.
For example you can check whether driver licence is valid or not by number and owner birthday. 

## Requirements

* PHP 7.1
* SOAP extension

## How to use

Fill your InMotiv credentials and driver license details in the code below: 

```php
$endpointProvider = new ProductionEndpointProvider();
$xmlBuilder = new XmlBuilder();
$client = new InMotivClient(
    $endpointProvider,
    $xmlBuilder,
    111111,
    'rdc111111999',
    'xxxXXXxxx'
);
var_dump($client->isDriverLicenceValid('1111111111', 1990, 1, 1));
```

And result should be

```
bool(true)
```

See full example in [example.php](example.php).

### Debug mode

Request and response headers and bodies can be printed by forcing debug mode of the client.
Notice the last optional argument:

```php
$client = new InMotivClient(
    $endpointProvider,
    $xmlBuilder,
    111111,
    'rdc111111999',
    'xxxXXXxxx',
    true
);
```

Now lots of details became visible.

## What is currently implemented

* driver licence check
* very basic vehicle details by numberplates

## How to test

Since InMotiv does not have open sandbox account, you have to create `.env` file in the project root with
your credentials. Also you need to provide a valid driver licence and numberplates details.

```
INMOTIV_CLIENT_NUMBER=111111
INMOTIV_USERNAME=rdc111111999
INMOTIV_PASSWORD=xxxXXXxxx

DRIVER_LICENCE_NUMBER=xxxxxxxxxx
BIRTHDAY_YEAR=2000
BIRTHDAY_MONTH=10
BIRTHDAY_DAY=10

NUMBERPLATES_MOTORCYCLE=05MMGG
NUMBERPLATES_CAR=XXAAAX

NUMBERPLATES_MOTORCYCLE_WITHOUT_FIRST_REGISTRATION_DATE=XXAAAX
```

Then just run `./vendor/bin/phpunit`. Everything should be green.
