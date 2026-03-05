<?php

declare(strict_types=1);

namespace FioApi;

use PHPUnit\Framework\Assert;

class UrlBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingTokenExceptionIsThrownForEmptyToken(): void
    {
        $this->expectException(\FioApi\Exceptions\MissingTokenException::class);

        new UrlBuilder('');
    }

    public function testTokenCanBeSetThroughConstructor(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame('token1', $urlBuilder->getToken());
    }

    public function testTokenCanBeChangedThroughSetter(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        $urlBuilder->setToken('token2');
        Assert::assertSame('token2', $urlBuilder->getToken());
    }

    public function testBuildPeriodsUrlReturnValidUrl(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame(
            'https://fioapi.fio.cz/v1/rest/periods/token1/2015-03-25/2015-03-31/transactions.json',
            $urlBuilder->buildPeriodsUrl(
                new \DateTimeImmutable('2015-03-25'),
                new \DateTimeImmutable('2015-03-31')
            )
        );
    }

    public function testBuildLastUrlReturnsValidUrl(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame(
            'https://fioapi.fio.cz/v1/rest/last/token1/transactions.json',
            $urlBuilder->buildLastUrl()
        );
    }

    public function testBuildSetLastIdUrlReturnsValidUrl(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame(
            'https://fioapi.fio.cz/v1/rest/set-last-id/token1/123/',
            $urlBuilder->buildSetLastIdUrl('123')
        );
    }

    public function testBuildUploadUrlReturnsValidUrl(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame(
            'https://fioapi.fio.cz/v1/rest/import/',
            $urlBuilder->buildUploadUrl()
        );
    }

    public function testBaseUrlCanBeSetThroughConstructor(): void
    {
        $urlBuilder = new UrlBuilder('token1', 'https://proxy.internal/fio');
        Assert::assertSame(
            'https://proxy.internal/fio/',
            $urlBuilder->getBaseUrl()
        );
        Assert::assertSame(
            'https://proxy.internal/fio/last/token1/transactions.json',
            $urlBuilder->buildLastUrl()
        );
    }

    public function testBaseUrlCanBeChangedThroughSetter(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        $urlBuilder->setBaseUrl('https://sandbox.fio.local/v1/rest');

        Assert::assertSame(
            'https://sandbox.fio.local/v1/rest/',
            $urlBuilder->getBaseUrl()
        );
        Assert::assertSame(
            'https://sandbox.fio.local/v1/rest/import/',
            $urlBuilder->buildUploadUrl()
        );
    }

    public function testEmptyBaseUrlThrowsInvalidArgumentException(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        $this->expectException(\InvalidArgumentException::class);

        $urlBuilder->setBaseUrl('  ');
    }
}
