<?php

declare(strict_types=1);

namespace FioApi\Upload\Entity;

use FioApi\Exceptions\InvalidResponseException;
use SimpleXMLElement;

class UploadResponse
{
    protected const SUCCESS = 'ok';

    protected readonly SimpleXMLElement $xml;

    public function __construct(string $xml)
    {
        // Keep libxml parse errors internal so a malformed response does not leak PHP warnings.
        $previousInternalErrors = libxml_use_internal_errors(true);
        try {
            $this->xml = new SimpleXMLElement($xml);
        } catch (\Exception $e) {
            throw new InvalidResponseException('The Fio API response is not valid XML.', $e->getCode(), $e);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousInternalErrors);
        }
    }

    public function getXml(): SimpleXMLElement
    {
        return $this->xml;
    }

    public function hasUploadSucceeded(): bool
    {
        return $this->getStatus() === self::SUCCESS;
    }

    public function getStatus(): string
    {
        return (string) $this->getResult()->status;
    }

    public function getCode(): int
    {
        return (int) $this->getResult()->errorCode;
    }

    public function getIdInstruction(): ?int
    {
        $idInstruction = $this->getResult()->idInstruction;
        if ($idInstruction->count() === 0 && trim((string) $idInstruction) === '') {
            return null;
        }

        return (int) $idInstruction;
    }

    protected function getResult(): SimpleXMLElement
    {
        return $this->xml->result;
    }
}
