<?php

declare(strict_types=1);

namespace FioApi;

use FioApi\Exceptions\MissingCertificateException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;

abstract class Transferrer
{
    protected UrlBuilder $urlBuilder;
    protected ?ClientInterface $client;
    protected ?string $certificatePath = null;
    protected ?float $requestTimeout = null;
    protected ?float $connectTimeout = null;
    protected int $retryCount = 0;
    protected int $retryInitialDelayMs = 30000;
    protected float $retryBackoffMultiplier = 2.0;
    protected int $retryMaxDelayMs = 30000;

    protected function __construct(
        string $token,
        ?ClientInterface $client = null
    ) {
        $this->urlBuilder = new UrlBuilder($token);
        $this->client = $client;
    }

    public function setCertificatePath(string $path): void
    {
        $this->certificatePath = $path;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->urlBuilder->setBaseUrl($baseUrl);
    }

    public function setRequestTimeout(float $seconds): void
    {
        if ($seconds <= 0) {
            throw new \InvalidArgumentException('Request timeout must be greater than 0 seconds.');
        }
        $this->requestTimeout = $seconds;
    }

    public function setConnectTimeout(float $seconds): void
    {
        if ($seconds <= 0) {
            throw new \InvalidArgumentException('Connect timeout must be greater than 0 seconds.');
        }
        $this->connectTimeout = $seconds;
    }

    public function configureRetry(
        int $retryCount,
        int $initialDelayMs = 30000,
        float $backoffMultiplier = 2.0,
        ?int $maxDelayMs = null
    ): void {
        if ($retryCount < 0) {
            throw new \InvalidArgumentException('Retry count must be zero or a positive integer.');
        }
        if ($initialDelayMs < 0) {
            throw new \InvalidArgumentException('Initial retry delay must be zero or a positive integer.');
        }
        if ($backoffMultiplier < 1.0) {
            throw new \InvalidArgumentException('Retry backoff multiplier must be at least 1.0.');
        }

        $effectiveMaxDelayMs = $maxDelayMs ?? ($initialDelayMs * 8);
        if ($effectiveMaxDelayMs < 0) {
            throw new \InvalidArgumentException('Maximum retry delay must be zero or a positive integer.');
        }
        if ($effectiveMaxDelayMs < $initialDelayMs) {
            throw new \InvalidArgumentException('Maximum retry delay must not be lower than initial retry delay.');
        }

        $this->retryCount = $retryCount;
        $this->retryInitialDelayMs = $initialDelayMs;
        $this->retryBackoffMultiplier = $backoffMultiplier;
        $this->retryMaxDelayMs = $effectiveMaxDelayMs;
    }

    public function getCertificatePath(): string
    {
        if ($this->certificatePath !== null) {
            return $this->validateCertificatePath($this->certificatePath);
        }

        if (class_exists('\Composer\CaBundle\CaBundle')) {
            return $this->validateCertificatePath(\Composer\CaBundle\CaBundle::getSystemCaRootBundlePath());
        } elseif (class_exists('\Kdyby\CurlCaBundle\CertificateHelper')) {
            return $this->validateCertificatePath(\Kdyby\CurlCaBundle\CertificateHelper::getCaInfoFile());
        }

        throw new MissingCertificateException(
            'No CA certificate bundle available. Install composer/ca-bundle or set certificate path manually.'
        );
    }

    private function validateCertificatePath(string $certificatePath): string
    {
        if ($certificatePath === '' || is_file($certificatePath) === false) {
            throw new MissingCertificateException(
                sprintf('CA certificate path "%s" does not exist.', $certificatePath)
            );
        }

        return $certificatePath;
    }

    public function getClient(): ClientInterface
    {
        if (isset($this->client) === false) {
            $this->client = new \GuzzleHttp\Client();
        }
        return $this->client;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function requestWithRetry(string $method, string $url, array $options = []): ResponseInterface
    {
        $requestOptions = $this->applyDefaultRequestOptions($options);
        $delayMs = $this->retryInitialDelayMs;

        for ($attempt = 0; $attempt <= $this->retryCount; $attempt++) {
            try {
                return $this->getClient()->request($method, $url, $requestOptions);
            } catch (ConnectException $e) {
                if ($attempt === $this->retryCount) {
                    throw $e;
                }
                $this->sleepMilliseconds($delayMs);
                $delayMs = $this->calculateNextRetryDelay($delayMs);
            }
        }

        throw new \LogicException('Request retry loop ended unexpectedly.');
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function applyDefaultRequestOptions(array $options): array
    {
        if ($this->requestTimeout !== null && array_key_exists('timeout', $options) === false) {
            $options['timeout'] = $this->requestTimeout;
        }
        if ($this->connectTimeout !== null && array_key_exists('connect_timeout', $options) === false) {
            $options['connect_timeout'] = $this->connectTimeout;
        }

        return $options;
    }

    private function calculateNextRetryDelay(int $currentDelayMs): int
    {
        if ($currentDelayMs >= $this->retryMaxDelayMs) {
            return $this->retryMaxDelayMs;
        }

        $nextDelay = (int) round($currentDelayMs * $this->retryBackoffMultiplier);
        if ($nextDelay < $currentDelayMs) {
            return $this->retryMaxDelayMs;
        }

        return min($nextDelay, $this->retryMaxDelayMs);
    }

    protected function sleepMilliseconds(int $milliseconds): void
    {
        if ($milliseconds <= 0) {
            return;
        }

        usleep($milliseconds * 1000);
    }
}
