<?php
declare(strict_types=1);

namespace InMotivClient;

use DOMDocument;
use InMotivClient\Exception\XmlBuilder\PlaceholderNotFoundException;
use InMotivClient\Exception\XmlBuilder\RequestXmlInvalidException;

class XmlBuilder
{
    public function renderHeader(string $username, string $password, string $nonce): string
    {
        $data = [
            'username' => $username,
            'password' => $password,
            'nonce' => $nonce,
        ];
        return $this->buildXml('soapHeader', $data);
    }

    public function buildRequestDocumentVerificatieSysteem(
        string $clientNumber,
        int $drivingLicenceNumber,
        string $birthday
    ): string {
        $data = [
            'rdc' => $clientNumber,
            'drivingLicenceNumber' => $drivingLicenceNumber,
            'driverBirthday' => $birthday,
        ];
        return $this->buildXml('documentVerificatieSysteem', $data);
    }

    public function buildRequestOpvragenVoertuigscanMSI(string $clientNumber, string $numberplate): string
    {
        $data = [
            'rdc' => $clientNumber,
            'numberplate' => $numberplate,
        ];
        return $this->buildXml('opvragenVoertuigscanMSI', $data);
    }

    /**
     * @throws PlaceholderNotFoundException
     */
    protected function buildXml(string $templateFilename, array $vars = []): string
    {
        $template = $this->loadTemplate($templateFilename);
        return $this->render($template, $vars);
    }

    protected function render(string $template, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $search = sprintf('{{ %s }}', $k);
            if (false === strpos($template, $search)) {
                $msg = sprintf('Placeholder for key %s not found in the template', $k);
                throw new PlaceholderNotFoundException($msg);
            }
            $template = str_replace($search, $v, $template);
        }

        $dom = new DOMDocument;
        if (!@$dom->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . $template)) {
            throw new RequestXmlInvalidException;
        }

        return $template;
    }

    protected function loadTemplate(string $filename): string
    {
        $path = sprintf(__DIR__ . '/../resources/xmlTemplates/%s.xml', $filename);
        return file_get_contents($path);
    }
}
