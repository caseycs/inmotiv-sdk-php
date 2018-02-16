<?php
declare(strict_types=1);

namespace InMotivClient;

use InMotivClient\Container\VehicleInfoContainer;
use InMotivClient\Exception\IncorrectFieldException;
use InMotivClient\Exception\SoapException;
use InMotivClient\Exception\UnexpectedResponseException;
use InMotivClient\Exception\VehicleNotFoundException;
use SimpleXMLElement;

class InMotivClient
{
    /** @var SoapClientWrapper[] */
    private $clients;

    /** @var XmlBuilder */
    private $xmlBuilder;

    /** @var string */
    private $clientNumber;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var bool */
    private $debug;

    /** @var EndpointProviderInterface */
    private $endpointProvider;

    public function __construct(
        EndpointProviderInterface $endpointProvider,
        XmlBuilder $xmlBuilder,
        string $clientNumber,
        string $username,
        string $password,
        bool $debug = false
    ) {
        $this->endpointProvider = $endpointProvider;
        $this->xmlBuilder = $xmlBuilder;

        $this->clientNumber = $clientNumber;
        $this->username = $username;
        $this->password = $password;

        $this->debug = $debug;
    }

    /**
     * @throws IncorrectFieldException
     * @throws SoapException
     */
    public function isDriverLicenceValid(
        int $drivingLicenceNumber,
        int $birthYear,
        int $birthMonth,
        int $birthDay
    ): bool {
        $birthday = sprintf('%04d%02d%02d', $birthYear, $birthMonth, $birthDay);
        $xml = $this->xmlBuilder->buildRequestDocumentVerificatieSysteem(
            $this->clientNumber,
            $drivingLicenceNumber,
            $birthday
        );

        $client = $this->getClient($this->endpointProvider->getDVS());
        $sax = $client->request('documentVerificatieSysteem', $xml);

        $nodes = $sax->xpath('//*[local-name() = "RIJBEWIJSGELDIG"]');
        if (!count($nodes)) {
            throw new UnexpectedResponseException('Expected node RIJBEWIJSGELDIG not found');
        }

        $value = (string)reset($nodes);
        return $value === 'J';
    }

    public function getVehicleInfo(string $numberplate): VehicleInfoContainer
    {
        $sxe = $this->makeVehicleInfoRequest($numberplate, (bool)getenv('INMOTIV_CACHE'));
        return $this->buildVehicleInfoContainer($sxe);
    }

    public function getVehicleInfoFromXML(string $xml): VehicleInfoContainer
    {
        $sxe = new SimpleXMLElement($xml);
        return $this->buildVehicleInfoContainer($sxe);
    }

    /**
     * @throws VehicleNotFoundException
     * @throws SoapException
     * @throws IncorrectFieldException
     */
    private function buildVehicleInfoContainer(SimpleXMLElement $sxe): VehicleInfoContainer
    {
        $nodes = $sxe->xpath('//*[local-name() = "Kentekengegevens"][@Verwerkingsstatus="00"]');
        if (!count($nodes)) {
            throw new VehicleNotFoundException;
        }

        $brand = $this->extractFirstNodeValue($sxe, '//*[local-name() = "Merk"]');
        try {
            $productionYear = $this->extractFirstNodeValue($sxe, '//*[local-name() = "DatumEersteToelating"]');
        } catch (UnexpectedResponseException $e) {
            $productionYear = null;
        }

        try {
            $cc = (int)$this->extractFirstNodeValue($sxe, '//*[local-name() = "Cilinderinhoud"]');
        } catch (UnexpectedResponseException $e) {
            $cc = null;
        }

        $horsePower = $this->extractFirstNodeValue($sxe, '//*[local-name() = "VermogenPK"]');
        $weight = $this->extractFirstNodeValue($sxe, '//*[local-name() = "MassaLeegVoertuig"]');
        try {
            $catalogPrice = (int)$this->extractFirstNodeValue($sxe, '//*[local-name() = "PrijsConsument"]');
        } catch (UnexpectedResponseException $e) {
            $catalogPrice = null;
        }

        $rdwClassSxe = $this->extractFirstNode($sxe, '//*[local-name() = "VoertuigClassificatieRDW"]');
        $rdwClass = (int)$rdwClassSxe->attributes()->Code;

        $rdwClassSxe = $this->extractFirstNode($sxe, '//*[local-name() = "StatusGestolen"]');
        $isStolen = (string)$rdwClassSxe->attributes()->Code !== '0';

        $result = new VehicleInfoContainer(
            $brand,
            $productionYear === null ? null : (int)substr($productionYear, 0, 4),
            $cc === null ? null : (int)$cc,
            (int)$horsePower,
            (int)$weight,
            $catalogPrice,
            $rdwClass,
            $isStolen,
            $sxe->saveXML()
        );

        return $result;
    }

    private function makeVehicleInfoRequest(string $numberplate, bool $useCache): SimpleXMLElement
    {
        $xml = $this->xmlBuilder->buildRequestOpvragenVoertuigscanMSI($this->clientNumber, $numberplate);
        $client = $this->getClient($this->endpointProvider->getVTS());

        if ($useCache) {
            $cachePath = __DIR__ . '/../cache/' . md5($numberplate);
            if (is_file($cachePath)) {
                return new SimpleXMLElement(file_get_contents($cachePath));
            }
        }

        $sxe = $client->request('opvragenVoertuigscanMSI', $xml);

        if ($useCache) {
            file_put_contents($cachePath, $sxe->saveXML());
        }

        return $sxe;
    }

    private function getClient(string $url): SoapClientWrapper
    {
        if (isset($this->clients[$url])) {
            return $this->clients[$url];
        }
        $this->clients[$url] = new SoapClientWrapper(
            $url,
            $this->username,
            $this->password,
            $this->xmlBuilder,
            $this->debug
        );
        return $this->clients[$url];
    }

    private function extractFirstNodeValue(SimpleXMLElement $sax, string $xpathExpression): string
    {
        return (string)$this->extractFirstNode($sax, $xpathExpression);
    }

    private function extractFirstNode(SimpleXMLElement $sax, string $xpathExpression): SimpleXMLElement
    {
        $nodes = $sax->xpath($xpathExpression);
        if (count($nodes) < 1) {
            $msg = sprintf('Expected at lest one node by expression: %s', $xpathExpression);
            throw new UnexpectedResponseException($msg);
        }
        return $nodes[0];
    }
}
