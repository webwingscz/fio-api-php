<?php

declare(strict_types=1);

namespace FioApi\Upload;

use FioApi\Exceptions\MissingPaymentOrderException;
use FioApi\Exceptions\UnexpectedPaymentOrderValueException;
use FioApi\Transferrer;
use FioApi\Upload\Entity\PaymentOrder;
use FioApi\Upload\Entity\PaymentOrderList;
use FioApi\Upload\Entity\UploadResponse;
use FioApi\Upload\FileBuilder\FileBuilder;
use FioApi\Upload\FileBuilder\XmlFileBuilder;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class Uploader extends Transferrer
{
    protected const ACCOUNT_FROM_MAX_LENGTH = 16;

    protected string $accountFrom;
    protected PaymentOrderList $paymentOrderList;

    public function __construct(
        #[\SensitiveParameter] string $token,
        string $accountFrom,
        ?ClientInterface $client = null,
        protected ?FileBuilder $fileBuilder = null,
    ) {
        parent::__construct($token, $client);
        $this->accountFrom = static::validateAccountFrom($accountFrom);
    }

    public function addPaymentOrder(PaymentOrder $paymentOrder): void
    {
        $this->getPaymentOrderList()->addPaymentOrder($paymentOrder);
    }

    public function uploadPaymentOrders(): UploadResponse
    {
        if ($this->getPaymentOrderList()->isEmpty()) {
            throw new MissingPaymentOrderException('You have to add at least one payment order before uploading.');
        }
        $response = $this->sendRequest();
        $this->getPaymentOrderList()->clear();

        return new UploadResponse($response->getBody()->getContents());
    }

    public function getPaymentOrderList(): PaymentOrderList
    {
        return $this->paymentOrderList ??= new PaymentOrderList();
    }

    public function getFileBuilder(): FileBuilder
    {
        return $this->fileBuilder ??= new XmlFileBuilder();
    }

    protected function sendRequest(): ResponseInterface
    {
        $url = $this->urlBuilder->buildUploadUrl();
        $fileBuilder = $this->getFileBuilder();

        return $this->requestWithRetry('POST', $url, [
            'verify' => $this->getCertificatePath(),
            'multipart' => [
                [
                    'name'     => 'token',
                    'contents' => $this->urlBuilder->getToken()
                ],
                [
                    'name'     => 'type',
                    'contents' => $fileBuilder->getFileType()
                ],
                [
                    'name'     => 'file',
                    'contents' => $fileBuilder->createFromPaymentOrderList(
                        $this->getPaymentOrderList(),
                        $this->accountFrom
                    ),
                    'filename' => 'request.' . $fileBuilder->getFileType()
                ],
                [
                    'name'     => 'lng',
                    'contents' => 'en'
                ],
            ]
        ]);
    }

    protected static function validateAccountFrom(string $account): string
    {
        if (ctype_digit($account) === false) {
            throw new UnexpectedPaymentOrderValueException(
                sprintf('Account "%s" has to contain digits only.', $account)
            );
        }
        if (strlen($account) > self::ACCOUNT_FROM_MAX_LENGTH) {
            throw new UnexpectedPaymentOrderValueException(
                sprintf('Account "%s" has to contain %s digits at maximum.', $account, self::ACCOUNT_FROM_MAX_LENGTH)
            );
        }
        return $account;
    }
}
