<?php
declare(strict_types=1);

namespace InMotivClient;

interface EndpointProviderInterface
{
    public function getDVS(): string;

    public function getVTS(): string;
}
