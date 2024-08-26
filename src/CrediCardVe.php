<?php

namespace CrediCardVe\CrediCardVe;

use CrediCardVe\CrediCardVe\Exceptions\CreditCardException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class CrediCardVe
{
    private Client $client;

    private ?string $accessToken = null;

    /**
     * @throws CreditCardException
     */
    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected string $baseUrl,
        protected bool $verifySsl = true
    ) {
        if (empty($clientId) || empty($clientSecret)) {
            throw new CreditCardException(
                'Los parámetros "CLIENT_ID" y "CLIENT_SECRET" son requeridos para procesar la petición.'
            );
        }

        if (empty($baseUrl)) {
            throw new CreditCardException(
                'La URL base del API es requerida para procesar la petición.'
            );
        }

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'verify' => $this->verifySsl,
        ]);
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getCardBankInfo(int $cardNumber): mixed
    {
        return $this->makeRequest('/payment/card_info', [
            'headers' => [
                'Authorization' => "Bearer {$this->getAccessToken()}",
            ],
            'query' => [
                'card_number' => $cardNumber,
            ],
        ], 'GET');
    }

    public function getCardHolderCommission(
        int $cardNumber,
        string $cardType,
        string $currency,
        float $amount
    ): mixed {
        return $this->makeRequest('/payment/card_holder_commission', [
            'headers' => [
                'Authorization' => "Bearer {$this->getAccessToken()}",
            ],
            'query' => [
                'card_number' => $cardNumber,
                'card_type' => $cardType,
                'currency' => $currency,
                'amount' => $amount,
            ],
        ], 'GET');
    }

    public function payUsingCard(array $paymentData): mixed
    {
        return $this->makeRequest('/payment', [
            'headers' => [
                'Authorization' => "Bearer {$this->getAccessToken()}",
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'payment_type' => 'CARD_PAYMENT',
            ],
            'json' => $paymentData,
        ], 'POST');
    }

    public function bankCardSendToken(string $bankCode, string $rif, string $phone): mixed
    {
        return $this->makeRequest('/payment/bank_card/send_token', [
            'headers' => [
                'Authorization' => "Bearer {$this->getAccessToken()}",
            ],
            'query' => [
                'bank_code' => $bankCode,
            ],
            'json' => [
                'phone' => $phone,
                'rif' => $rif,
            ],
        ], 'POST');
    }

    public function sendBankCardValidationToken(array $creditCardData): mixed
    {
        return $this->makeRequest('/payment/send_token_with_card', [
            'headers' => [
                'Authorization' => "Bearer {$this->getAccessToken()}",
            ],
            'json' => [
                'credit_card' => $creditCardData,
            ],
        ], 'POST');
    }

    public function transactionReports(array $params): mixed
    {
        return $this->makeRequest('/payment/transaction_report', [
            'headers' => [
                'Authorization' => "Bearer {$this->getAccessToken()}",
            ],
            'query' => $params,
        ], 'GET');
    }

    private function getAccessToken(): string
    {
        if ($this->accessToken === null) {
            $response = $this->requestToken();
            $this->accessToken = $response['access_token'] ?? throw new RuntimeException('No se pudo obtener el token de acceso.');
        }

        return $this->accessToken;
    }

    private function requestToken(): mixed
    {
        return $this->makeRequest('/oauth/authorize', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'query' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ], 'POST');
    }

    private function makeRequest(string $url, array $args, string $method): mixed
    {
        try {
            $response = $this->client->request($method, $url, $args);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Error al decodificar la respuesta JSON: ' . json_last_error_msg());
            }

            return $responseData;
        } catch (GuzzleException $e) {
            throw new RuntimeException('Error durante la solicitud HTTP: ' . $e->getMessage(), 0, $e);
        }
    }
}
