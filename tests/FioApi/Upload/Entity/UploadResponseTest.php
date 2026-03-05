<?php

declare(strict_types=1);

namespace FioApi\Upload\Entity;

class UploadResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testHasUploadSucceededReturnsTrueIfSuccessResponse(): void
    {
        $response = new UploadResponse($this->readFixture('example-response-success.xml'));

        self::assertTrue($response->hasUploadSucceeded());
    }

    public function testHasUploadSucceededReturnsFalseIfErrorResponse(): void
    {
        $response = new UploadResponse($this->readFixture('example-response-error.xml'));

        self::assertFalse($response->hasUploadSucceeded());
    }

    public function testGetCodeReturnsErrorCodeIfErrorResponse(): void
    {
        $response = new UploadResponse($this->readFixture('example-response-error.xml'));

        self::assertSame(1, $response->getCode());
    }

    public function testGetIdInstructionReturnsInstructionIdIfSuccessResponse(): void
    {
        $response = new UploadResponse($this->readFixture('example-response-success.xml'));

        self::assertSame(1385186, $response->getIdInstruction());
    }

    public function testGetIdInstructionReturnsNullIfErrorResponse(): void
    {
        $response = new UploadResponse($this->readFixture('example-response-error.xml'));

        self::assertNull($response->getIdInstruction());
    }

    private function readFixture(string $fixture): string
    {
        $content = file_get_contents(__DIR__ . '/../data/' . $fixture);
        self::assertIsString($content);

        return $content;
    }
}
