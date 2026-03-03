<?php
declare(strict_types = 1);

namespace FioApi\Upload;

use FioApi\Exceptions\MissingPaymentOrderException;
use FioApi\Exceptions\UnexpectedPaymentOrderValueException;
use FioApi\Upload\Entity\PaymentOrderCzech;
use FioApi\Upload\Entity\UploadResponse;
use FioApi\Upload\FileBuilder\FileBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
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
