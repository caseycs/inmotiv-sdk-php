<?php
declare(strict_types=1);

namespace InMotivClient;

class SandboxEndpointProvider implements EndpointProviderInterface
{
    public function getDVS(): string
    {
        return 'https://acc-services.rdc.nl/dvs/1.0/acc/wsdl';
    }

    public function getVTS(): string
    {
        return 'https://acc-services.rdc.nl/voertuigscan/2.0/acc/wsdl';
    }
}
