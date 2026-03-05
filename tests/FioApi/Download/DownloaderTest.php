<?php
declare(strict_types = 1);

namespace FioApi\Download;

use FioApi\Exceptions\ConnectionException;
use FioApi\Exceptions\InvalidResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;

class DownloaderTest extends \PHPUnit\Framework\TestCase
{
    public function testNotRespectingTheTimeoutResultsInTooGreedyException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(409),
        ]));
        $downloader = new Downloader('testToken', new Client(['handler' => $handler]));

        $this->expectException(\FioApi\Exceptions\TooGreedyException::class);

        $downloader->downloadSince(new \DateTimeImmutable('-1 week'));
    }

    public function testInvalidTokenResultsInInternalErrorException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(500),
        ]));
        $downloader = new Downloader('invalidToken', new Client(['handler' => $handler]));

        $this->expectException(\FioApi\Exceptions\InternalErrorException::class);

        $downloader->downloadSince(new \DateTimeImmutable('-1 week'));
    }

    public function testUnknownResponseCodePassesOriginalException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(418),
        ]));
        $downloader = new Downloader('validToken', new Client(['handler' => $handler]));

        $this->expectException(\GuzzleHttp\Exception\BadResponseException::class);
        $this->expectExceptionCode(418);

        $downloader->downloadSince(new \DateTimeImmutable('-1 week'));
    }

    public function testDownloaderDownloadsData(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], (string) file_get_contents(__DIR__ . '/data/example-response.json')),
        ]));
        $downloader = new Downloader('validToken', new Client(['handler' => $handler]));

        $transactionList = $downloader->downloadSince(new \DateTimeImmutable('-1 week'));

        Assert::assertCount(3, $transactionList->getTransactions());
    }

    public function testDownloaderDownloadsLast(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], (string) file_get_contents(__DIR__ . '/data/example-response.json')),
        ]));
        $downloader = new Downloader('validToken', new Client(['handler' => $handler]));

        $transactionList = $downloader->downloadLast();

        Assert::assertCount(3, $transactionList->getTransactions());
    }

    public function testDownloaderSetsLastId(): void
    {
        /** @var array<int, array{request: \Psr\Http\Message\RequestInterface}> $container */
        $container = [];
        $history = Middleware::history($container);

        $handler = HandlerStack::create(new MockHandler([
            new Response(200),
        ]));
        $handler->push($history);
        $downloader = new Downloader('validToken', new Client(['handler' => $handler]));

        $downloader->setLastId('123456');

        Assert::assertIsArray($container);
        Assert::assertCount(1, $container);

        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = $container[0]['request'];

        Assert::assertSame('https://fioapi.fio.cz/v1/rest/set-last-id/validToken/123456/', (string) $request->getUri());
    }

    public function testConnectionIssueResultsInConnectionException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new ConnectException('Connection timed out', new Request('GET', '/')),
        ]));
        $downloader = new Downloader('validToken', new Client(['handler' => $handler]));

        $this->expectException(ConnectionException::class);

        $downloader->downloadSince(new \DateTimeImmutable('-1 week'));
    }

    public function testDownloaderRetriesConnectionExceptionWhenConfigured(): void
    {
        /** @var array<int, array{request: \Psr\Http\Message\RequestInterface}> $container */
        $container = [];
        $history = Middleware::history($container);
        $handler = HandlerStack::create(new MockHandler([
            new ConnectException('Connection timed out', new Request('GET', '/')),
            new Response(200, [], (string) file_get_contents(__DIR__ . '/data/example-response.json')),
        ]));
        $handler->push($history);
        $downloader = new Downloader('validToken', new Client(['handler' => $handler]));
        $downloader->configureRetry(1, 0);

        $transactionList = $downloader->downloadSince(new \DateTimeImmutable('-1 week'));

        Assert::assertCount(3, $transactionList->getTransactions());
        Assert::assertCount(2, (array) $container);
    }

    public function testConfiguredTimeoutsArePassedToRequestOptions(): void
    {
        /** @var array<int, array{options: array<string, mixed>}> $container */
        $container = [];
        $history = Middleware::history($container);
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], (string) file_get_contents(__DIR__ . '/data/example-response.json')),
        ]));
        $handler->push($history);
        $downloader = new Downloader('validToken', new Client(['handler' => $handler]));
        $downloader->setRequestTimeout(10.5);
        $downloader->setConnectTimeout(2.5);

        $downloader->downloadSince(new \DateTimeImmutable('-1 week'));

        Assert::assertCount(1, (array) $container);
        Assert::assertSame(10.5, $container[0]['options']['timeout']);
        Assert::assertSame(2.5, $container[0]['options']['connect_timeout']);
    }

    public function testInvalidJsonResponseResultsInInvalidResponseException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{invalid-json'),
        ]));
        $downloader = new Downloader('validToken', new Client(['handler' => $handler]));

        $this->expectException(InvalidResponseException::class);

        $downloader->downloadSince(new \DateTimeImmutable('-1 week'));
    }

    public function testMissingAccountStatementResultsInInvalidResponseException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"foo":"bar"}'),
        ]));
        $downloader = new Downloader('validToken', new Client(['handler' => $handler]));

        $this->expectException(InvalidResponseException::class);

        $downloader->downloadSince(new \DateTimeImmutable('-1 week'));
    }
}
