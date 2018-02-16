<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use InMotivClient\InMotivClient;
use InMotivClient\ProductionEndpointProvider;
use InMotivClient\XmlBuilder;

require 'vendor/autoload.php';

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$endpointProvider = new ProductionEndpointProvider();
$xmlBuilder = new XmlBuilder();

$client = new InMotivClient(
    $endpointProvider,
    $xmlBuilder,
    getenv('INMOTIV_CLIENT_NUMBER'),
    getenv('INMOTIV_USERNAME'),
    getenv('INMOTIV_PASSWORD'),
    true
);

//driver licence check
$result = $client->isDriverLicenceValid(
    (int)getenv('DRIVER_LICENCE_NUMBER'),
    (int)getenv('BIRTHDAY_YEAR'),
    (int)getenv('BIRTHDAY_MONTH'),
    (int)getenv('BIRTHDAY_DAY')
);

var_dump($result);

//vehicle info
$result = $client->getVehicleInfo(getenv('NUMBERPLATES_CAR'));

var_dump($result);
