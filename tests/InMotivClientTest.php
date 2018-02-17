<?php
declare(strict_types=1);

use InMotivClient\Container\VehicleInfoContainer;
use InMotivClient\InMotivClient;
use InMotivClient\ProductionEndpointProvider;
use InMotivClient\SandboxEndpointProvider;
use InMotivClient\XmlBuilder;

class InMotivClientTest extends PHPUnit_Framework_TestCase
{
    public function testIsDriverLicenceValidValid(): void
    {
        $this->assertTrue(
            $this->getProductionClient()->isDriverLicenceValid(
                (int)getenv('DRIVER_LICENCE_NUMBER'),
                (int)getenv('BIRTHDAY_YEAR'),
                (int)getenv('BIRTHDAY_MONTH'),
                (int)getenv('BIRTHDAY_DAY')
            )
        );
    }

    public function testIsDriverLicenceValidInvalid(): void
    {
        $this->assertFalse(
            $this->getProductionClient()->isDriverLicenceValid(
                (int)str_repeat('1', strlen(getenv('DRIVER_LICENCE_NUMBER'))),
                (int)getenv('BIRTHDAY_YEAR'),
                (int)getenv('BIRTHDAY_MONTH'),
                (int)getenv('BIRTHDAY_DAY')
            )
        );
    }

    /**
     * @expectedException \InMotivClient\Exception\IncorrectFieldException
     */
    public function testIsDriverLicenceValidWrongDate(): void
    {
        $this->assertTrue(
            $this->getProductionClient()->isDriverLicenceValid(
                (int)getenv('DRIVER_LICENCE_NUMBER'),
                99,
                (int)getenv('BIRTHDAY_MONTH'),
                (int)getenv('BIRTHDAY_DAY')
            )
        );
    }

    public function testVehicleInfoSuccessCar(): void
    {
        $result = $this->getProductionClient()->getVehicleInfo(getenv('NUMBERPLATES_CAR'));
        $this->assertInstanceOf(VehicleInfoContainer::class, $result);
        $this->assertSame('SKODA', $result->getBrand());
        $this->assertSame(1197, $result->getEngineCC());
        $this->assertSame(2011, $result->getProductionYear());
        $this->assertSame(105, $result->getHorsePower());
        $this->assertSame(1205, $result->getWeight());
        $this->assertSame(25630, $result->getCatalogPrice());
        $this->assertFalse($result->isMotorcycle());
        $this->assertFalse($result->isStolen());
    }

    public function testVehicleInfoSuccessMotorcycleNormal(): void
    {
        $result = $this->getProductionClient()->getVehicleInfo(getenv('NUMBERPLATES_MOTORCYCLE'));
        $this->assertInstanceOf(VehicleInfoContainer::class, $result);
        $this->assertSame('HONDA', $result->getBrand());
        $this->assertSame(647, $result->getEngineCC());
        $this->assertSame(2005, $result->getProductionYear());
        $this->assertSame(53, $result->getHorsePower());
        $this->assertSame(221, $result->getWeight());
        $this->assertSame(null, $result->getCatalogPrice());
        $this->assertTrue($result->isMotorcycle());
        $this->assertFalse($result->isStolen());
    }

    public function testVehicleInfoSuccessMotorcycleElectric(): void
    {
        $result = $this->getProductionClient()->getVehicleInfo(getenv('NUMBERPLATES_MOTORCYCLE_ELECTRIC'));
        $this->assertInstanceOf(VehicleInfoContainer::class, $result);
        $this->assertSame('ENERGICA', $result->getBrand());
        $this->assertSame(null, $result->getEngineCC());
        $this->assertSame(2016, $result->getProductionYear());
        $this->assertSame(0, $result->getHorsePower());
        $this->assertSame(282, $result->getWeight());
        $this->assertSame(null, $result->getCatalogPrice());
        $this->assertTrue($result->isMotorcycle());
        $this->assertFalse($result->isStolen());
    }

    public function testVehicleInfoSuccessMotorcycleWithoutFirstRegistrationDate(): void
    {
        $result = $this->getProductionClient()->getVehicleInfo(
            getenv('NUMBERPLATES_MOTORCYCLE_WITHOUT_FIRST_REGISTRATION_DATE')
        );
        $this->assertInstanceOf(VehicleInfoContainer::class, $result);
        $this->assertSame('KTM', $result->getBrand());
        $this->assertSame(373, $result->getEngineCC());
        $this->assertSame(null, $result->getProductionYear());
        $this->assertSame(44, $result->getHorsePower());
        $this->assertSame(159, $result->getWeight());
        $this->assertSame(null, $result->getCatalogPrice());
        $this->assertTrue($result->isMotorcycle());
        $this->assertFalse($result->isStolen());
    }

    /**
     * @expectedException \InMotivClient\Exception\VehicleNotFoundException
     */
    public function testVehicleInfoFail(): void
    {
        $this->getProductionClient()->getVehicleInfo(str_repeat('1', strlen(getenv('NUMBERPLATES_CAR'))));
    }

    /**
     * @expectedException \InMotivClient\Exception\XmlBuilder\RequestXmlInvalidException
     */
    public function testVehicleInfoInvalidRequestXml(): void
    {
        $this->getProductionClient()->getVehicleInfo('invalid < xml & value');
    }

    private function getProductionClient(): InMotivClient
    {
        $endpointProvider = new ProductionEndpointProvider();
        $xmlBuilder = new XmlBuilder();
        return new InMotivClient(
            $endpointProvider,
            $xmlBuilder,
            getenv('INMOTIV_CLIENT_NUMBER'),
            getenv('INMOTIV_USERNAME'),
            getenv('INMOTIV_PASSWORD'),
            false
        );
    }

    private function getSandboxClient(): InMotivClient
    {
        $endpointProvider = new SandboxEndpointProvider();
        $xmlBuilder = new XmlBuilder();
        return new InMotivClient(
            $endpointProvider,
            $xmlBuilder,
            getenv('INMOTIV_CLIENT_NUMBER'),
            getenv('INMOTIV_USERNAME'),
            getenv('INMOTIV_PASSWORD'),
            false
        );
    }
}
