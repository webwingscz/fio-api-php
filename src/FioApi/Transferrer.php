<?php
declare(strict_types = 1);

namespace FioApi;

use FioApi\Exceptions\MissingCertificateException;
use GuzzleHttp\ClientInterface;

abstract class Transferrer
{
    protected UrlBuilder $urlBuilder;
    protected ?ClientInterface $client;
    protected ?string $certificatePath = null;

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
            throw new MissingCertificateException(sprintf('CA certificate path "%s" does not exist.', $certificatePath));
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
}
