<?php

declare(strict_types=1);

namespace FioApi\Upload;

use FioApi\Exceptions\MissingPaymentOrderException;
use FioApi\Exceptions\MissingCertificateException;
use FioApi\Exceptions\UnexpectedPaymentOrderValueException;
use FioApi\Upload\Entity\PaymentOrderCzech;
use FioApi\Upload\Entity\UploadResponse;
use FioApi\Upload\FileBuilder\FileBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class UploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider accountFromProvider
     */
    public function testInvalidAccountFromResultsInUnexpectedPaymentOrderValueException(string $accountFrom): void
    {
        $this->expectException(UnexpectedPaymentOrderValueException::class);

        new Uploader('testToken', $accountFrom);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function accountFromProvider(): array
    {
        return [
            'not only digits' => [ '12345489x' ],
            'too long' => [ '12345678901234567' ],
        ];
    }

    public function testAddPaymentOrderToUploader(): void
    {
        $uploader = new Uploader('testToken', '123456489');
        $uploader->addPaymentOrder($this->createStub(PaymentOrderCzech::class));

        self::assertFalse($uploader->getPaymentOrderList()->isEmpty());
    }

    public function testUploadingWithoutPaymentOrderResultsInMissingPaymentOrderException(): void
    {
        $uploader = new Uploader('testToken', '123456489');

        $this->expectException(MissingPaymentOrderException::class);

        $uploader->uploadPaymentOrders();
    }

    public function testMissingCertificateResultsInException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], $this->readFixture('example-response-success.xml')),
        ]));
        $uploader = new Uploader(
            'testToken',
            '123456489',
            new Client(['handler' => $handler]),
            $this->createStub(FileBuilder::class)
        );
        $uploader->setCertificatePath('/this/path/does/not/exist.pem');
        $uploader->addPaymentOrder($this->createStub(PaymentOrderCzech::class));

        $this->expectException(MissingCertificateException::class);

        $uploader->uploadPaymentOrders();
    }

    public function testUploaderUploadsPaymentOrders(): Uploader
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], $this->readFixture('example-response-success.xml')),
        ]));
        $uploader = new Uploader(
            'testToken',
            '123456489',
            new Client(['handler' => $handler]),
            $this->createStub(FileBuilder::class)
        );
        $uploader->addPaymentOrder($this->createStub(PaymentOrderCzech::class));
        $response = $uploader->uploadPaymentOrders();

        self::assertSame(UploadResponse::class, get_class($response));

        return $uploader;
    }

    public function testUploaderUploadCanFailWithConnectionException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new ConnectException('Connection timed out', new Request('POST', '/')),
        ]));
        $uploader = new Uploader(
            'testToken',
            '123456489',
            new Client(['handler' => $handler]),
            $this->createStub(FileBuilder::class)
        );
        $uploader->setCertificatePath(__FILE__);
        $uploader->addPaymentOrder($this->createStub(PaymentOrderCzech::class));

        $this->expectException(ConnectException::class);

        $uploader->uploadPaymentOrders();
    }

    public function testUploaderUploadCanFailWithServerException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(500),
        ]));
        $uploader = new Uploader(
            'testToken',
            '123456489',
            new Client(['handler' => $handler]),
            $this->createStub(FileBuilder::class)
        );
        $uploader->setCertificatePath(__FILE__);
        $uploader->addPaymentOrder($this->createStub(PaymentOrderCzech::class));

        $this->expectException(ServerException::class);

        $uploader->uploadPaymentOrders();
    }

    public function testUploaderUploadFailsForInvalidXmlResponse(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '<invalid'),
        ]));
        $uploader = new Uploader(
            'testToken',
            '123456489',
            new Client(['handler' => $handler]),
            $this->createStub(FileBuilder::class)
        );
        $uploader->setCertificatePath(__FILE__);
        $uploader->addPaymentOrder($this->createStub(PaymentOrderCzech::class));

        $this->expectException(\Exception::class);

        $uploader->uploadPaymentOrders();
    }

    /**
     * @depends testUploaderUploadsPaymentOrders
     */
    public function testUploaderClearPaymentOrdersAfterUpload(Uploader $uploader): void
    {
        self::assertTrue($uploader->getPaymentOrderList()->isEmpty());
    }

    private function readFixture(string $fixture): string
    {
        $content = file_get_contents(__DIR__ . '/data/' . $fixture);
        self::assertIsString($content);

        return $content;
    }
}
