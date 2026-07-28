<?php

declare(strict_types=1);

namespace FioApi\Fixture;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

/**
 * A PSR-18 network failure that is not a GuzzleHttp\Exception\ConnectException.
 *
 * Guzzle 8 classifies transport failures by phase, so a read timeout arrives as
 * NetworkTimeoutException, which does not extend ConnectException. The library has to
 * treat every no-response failure the same way, so it catches NetworkExceptionInterface.
 */
final class NetworkFailure extends \RuntimeException implements NetworkExceptionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        string $message = 'Connection timed out',
    ) {
        parent::__construct($message);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
