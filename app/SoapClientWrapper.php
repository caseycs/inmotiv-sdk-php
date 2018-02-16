<?php
declare(strict_types=1);

namespace InMotivClient;

use Exception;
use InMotivClient\Exception\IncorrectFieldException;
use InMotivClient\Exception\SoapException;
use SimpleXMLElement;
use SoapClient;
use SoapFault;
use SoapHeader;
use SoapVar;

class SoapClientWrapper
{
    const HEADER_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $password;

    /** @var SoapClient */
    private $soapClient;

    /** @var XmlBuilder */
    private $xmlBuilder;

    /** @var bool */
    private $debug;

    public function __construct(string $url, string $username, string $password, XmlBuilder $xmlBuilder, bool $debug)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;

        $this->soapClient = new SoapClient($url, ['trace' => 1]);
        $this->xmlBuilder = $xmlBuilder;
        $this->debug = $debug;
    }

    /**
     * @throws SoapException
     * @throws IncorrectFieldException
     */
    public function request(string $method, string $xml): SimpleXMLElement
    {
        try {
            $this->soapClient->__setSoapHeaders($this->generateWSSecurityHeader());
            $params = new SoapVar($xml, XSD_ANYXML);
            $this->soapClient->__call($method, [$params]);

            $response = $this->soapClient->__getLastResponse();

            if ($this->debug) {
                $this->printLastRequestDebug($method);
            }

            $xml = new SimpleXMLElement($response);
            return $xml;
        } catch (Exception $exception) {
            if ($exception instanceof SoapFault && $exception->getMessage() === '1534') {
                throw new IncorrectFieldException('', 0, $exception);
            }

            if ($this->debug) {
                $this->printLastRequestDebug($method);
            }

            throw new SoapException($this->url, $method, $exception);
        }
    }

    private function printLastRequestDebug(string $method): void
    {
        printf('REQUEST %s', $method);
        echo PHP_EOL;
        echo $this->soapClient->__getLastRequestHeaders();
        echo $this->soapClient->__getLastRequest();
        echo PHP_EOL;
        echo 'RESPONSE:' . PHP_EOL;
        echo $this->soapClient->__getLastResponseHeaders();
        echo $this->soapClient->__getLastResponse();
        echo PHP_EOL . PHP_EOL;
    }

    private function generateWSSecurityHeader(): SoapHeader
    {
        $xml = $this->xmlBuilder->renderHeader($this->username, $this->password, sha1((string)mt_rand()));
        return new SoapHeader(self::HEADER_NAMESPACE, 'Security', new SoapVar($xml, XSD_ANYXML));
    }
}
