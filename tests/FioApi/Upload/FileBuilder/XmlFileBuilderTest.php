<?php
declare(strict_types = 1);

namespace FioApi\Upload\FileBuilder;

use FioApi\Upload\Entity\PaymentOrderCzech;
use FioApi\Upload\Entity\PaymentOrderEuro;
use FioApi\Upload\Entity\PaymentOrderInternational;
use FioApi\Upload\Entity\PaymentOrderList;

class XmlFileBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testXmlFileBuilderCreatesCorrectXmlFromPaymentOrderList(): void
    {
        $paymentOrderCzech = new PaymentOrderCzech(
            'CZK',
            100.0,
            '2212-2000000699',
            '0300',
            new \DateTimeImmutable('2021-07-22'),
            '0558',
            '1234567890',
            '0987654321',
            'message',
            'comment',
            110,
            431001
        );

        $paymentOrderEuro = new PaymentOrderEuro(
            'EUR',
            50.53,
            'AT611904300234573201',
            new \DateTimeImmutable('2021-07-22'),
            'Hans Gruber',
            'Gugitzgasse 2',
            'Wien',
            'AT',
            '0558',
            '1234567890',
            '0987654321',
            'ABAGATWWXXX',
            'comment',
            'info1',
            'info2',
            'info3',
            520,
            431008
        );

        $paymentOrderInternational = new PaymentOrderInternational(
            'USD',
            50.53,
            'PK36SCBL0000001123456702',
            'ALFHPKKAXXX',
            new \DateTimeImmutable('2021-07-22'),
            'Amir Khan',
            'Nishtar Rd 13',
            'Karachi',
            'PK',
            470502,
            952,
            'info1',
            'info2',
            'info3',
            'info4',
            'comment'
        );

        $paymentOrderList = new PaymentOrderList();
        $paymentOrderList->addPaymentOrder($paymentOrderEuro)
            ->addPaymentOrder($paymentOrderInternational)
            ->addPaymentOrder($paymentOrderCzech);

        $xmlFileBuilder = new XmlFileBuilder();

        self::assertXmlStringEqualsXmlFile(
            __DIR__ . '/../data/example-request.xml',
            $xmlFileBuilder->createFromPaymentOrderList($paymentOrderList, '1234562')
        );
    }

    public function testXmlFileBuilderEscapesSpecialCharactersOnlyOnce(): void
    {
        $paymentOrderList = new PaymentOrderList();
        $paymentOrderList->addPaymentOrder(new PaymentOrderCzech(
            'CZK',
            100.0,
            '2212-2000000699',
            '0300',
            new \DateTimeImmutable('2021-07-22'),
            null,
            null,
            null,
            'A & B <C>',
            'note "quoted" & raw'
        ));

        $xml = (new XmlFileBuilder())->createFromPaymentOrderList($paymentOrderList, '1234562');

        self::assertStringContainsString('<messageForRecipient>A &amp; B &lt;C&gt;</messageForRecipient>', $xml);
        self::assertStringContainsString('<comment>note "quoted" &amp; raw</comment>', $xml);
        self::assertStringNotContainsString('&amp;amp;', $xml);
        self::assertStringNotContainsString('&amp;lt;', $xml);
    }
}
