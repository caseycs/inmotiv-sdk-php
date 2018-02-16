<?php
declare(strict_types=1);

namespace InMotivClient;

class ProductionEndpointProvider implements EndpointProviderInterface
{
    public function getDVS(): string
    {
        return 'https://services.rdc.nl/dvs/1.0/wsdl';
    }

    public function getVTS(): string
    {
        return 'https://services.rdc.nl/voertuigscan/2.0/wsdl';
    }
}
