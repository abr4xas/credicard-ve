<?php

use CrediCardVe\CrediCardVe\CrediCardVe;
use CrediCardVe\CrediCardVe\Exceptions\CreditCardException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

beforeEach(/**
 * @throws CreditCardException
 */ function () {
    $this->clientId = 'test-client-id';
    $this->clientSecret = 'test-client-secret';
    $this->baseUrl = 'https://api.example.com';
    $this->client = Mockery::mock(Client::class);
    $this->creditCardVe = new CrediCardVe($this->clientId, $this->clientSecret, $this->baseUrl);
    $this->creditCardVe->setClient($this->client);
});

function mockTokenRequest($client): void
{
    $expectedTokenResponse = ['access_token' => 'some-token'];
    $client->shouldReceive('request')
        ->with('POST', '/oauth/authorize', Mockery::subset([
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'query' => [
                'grant_type' => 'client_credentials',
                'client_id' => 'test-client-id',
                'client_secret' => 'test-client-secret',
            ],
        ]))
        ->andReturn(new Response(200, [], json_encode($expectedTokenResponse)));
}

it('throws an exception if client ID or secret is empty', function () {
    expect(fn () => new CrediCardVe('', '', $this->baseUrl))
        ->toThrow(CreditCardException::class);
});

it('throws an exception if base URL is empty', function () {
    expect(fn () => new CrediCardVe($this->clientId, $this->clientSecret, ''))
        ->toThrow(CreditCardException::class);
});

it('gets card bank info', function () {
    $cardNumber = 1234567890123456;
    $expectedResponse = [
        'financial_card_emitter' => ['name' => 'VISA', 'thumbnail' => 'images/issuingcard/visa-icon.png'],
        'otp_ccr_config' => [],
        'card_status' => 'LOCKED',
    ];

    mockTokenRequest($this->client);

    $this->client->shouldReceive('request')
        ->with('GET', '/payment/card_info', Mockery::subset([
            'headers' => ['Authorization' => 'Bearer some-token'],
            'query' => ['card_number' => $cardNumber],
        ]))
        ->andReturn(new Response(200, [], json_encode($expectedResponse)));

    $response = $this->creditCardVe->getCardBankInfo($cardNumber);

    expect($response)->toBeArray()
        ->and($response['financial_card_emitter']['name'])->toBe('VISA')
        ->and($response['card_status'])->toBe('LOCKED')
        ->and($response['otp_ccr_config'])->toBeArray();
});

it('gets card holder commission', function () {
    $cardNumber = 1234567890123456;
    $cardType = 'TDC';
    $currency = 'USD';
    $amount = 100.0;
    $expectedResponse = ['commission_amount' => 2.5, 'currency' => 'USD'];

    mockTokenRequest($this->client);

    $this->client->shouldReceive('request')
        ->with('GET', '/payment/card_holder_commission', Mockery::subset([
            'headers' => ['Authorization' => 'Bearer some-token'],
            'query' => [
                'card_number' => $cardNumber,
                'card_type' => $cardType,
                'currency' => $currency,
                'amount' => $amount,
            ],
        ]))
        ->andReturn(new Response(200, [], json_encode($expectedResponse)));

    $response = $this->creditCardVe->getCardHolderCommission($cardNumber, $cardType, $currency, $amount);

    expect($response)->toBeArray()
        ->and($response['commission_amount'])->toBe(2.5)
        ->and($response['currency'])->toBe('USD');
});

it('sends bank card validation token', function () {
    $bankCode = '0102';
    $rif = 'V016673906';
    $phone = '4241111111';
    $expectedResponse = [
        'code' => 200,
        'message' => 'BANK_CARD_VALIDATION_TOKEN_SENT',
        'cause' => [],
    ];

    mockTokenRequest($this->client);

    $this->client->shouldReceive('request')
        ->with('POST', '/payment/bank_card/send_token', Mockery::subset([
            'headers' => ['Authorization' => 'Bearer some-token'],
            'query' => ['bank_code' => $bankCode],
            'json' => ['phone' => $phone, 'rif' => $rif],
        ]))
        ->andReturn(new Response(200, [], json_encode($expectedResponse)));

    $response = $this->creditCardVe->bankCardSendToken($bankCode, $rif, $phone);

    expect($response)->toBeArray()
        ->and($response['code'])->toBe(200)
        ->and($response['message'])->toBe('BANK_CARD_VALIDATION_TOKEN_SENT')
        ->and($response['cause'])->toBeArray();
});

it('sends bank card validation token with card data', function () {
    $creditCardData = [
        'holder_name' => 'DHARRYLX',
        'card_number' => '4222610122997125',
        'holder_id' => 'V004000004',
        'holder_id_doc' => 'RIF',
        'expiration_month' => 12,
        'expiration_year' => 24,
        'cvc' => '808',
        'currency' => 'USD',
        'card_type' => 'CREDIT',
    ];

    $expectedResponse = ['code' => 200, 'message' => 'NO_OTP_NEEDED', 'cause' => []];

    mockTokenRequest($this->client);

    $this->client->shouldReceive('request')
        ->with('POST', '/payment/send_token_with_card', Mockery::subset([
            'headers' => ['Authorization' => 'Bearer some-token'],
            'json' => ['credit_card' => $creditCardData],
        ]))
        ->andReturn(new Response(200, [], json_encode($expectedResponse)));

    $response = $this->creditCardVe->sendBankCardValidationToken($creditCardData);

    expect($response)->toBeArray()
        ->and($response['code'])->toBe(200)
        ->and($response['message'])->toBe('NO_OTP_NEEDED')
        ->and($response['cause'])->toBeArray();
});

it('gets transaction reports', function () {
    $params = [
        'begin' => '2024-01-01',
        'end' => '2024-01-31',
        'time_zone' => 'America/Caracas',
        'affiliation' => '10000000',
        'status_id' => 'PAY',
        'status' => 'APPROVED',
    ];

    $expectedResponse = [
        'first_page' => 'offset=0&limit=10000',
        'last_page' => 'offset=0&limit=10000',
        'count' => 100,
        'results' => [],
    ];

    mockTokenRequest($this->client);

    $this->client->shouldReceive('request')
        ->with('GET', '/payment/transaction_report', Mockery::subset([
            'headers' => ['Authorization' => 'Bearer some-token'],
            'query' => $params,
        ]))
        ->andReturn(new Response(200, [], json_encode($expectedResponse)));

    $response = $this->creditCardVe->transactionReports($params);

    expect($response)->toBeArray()
        ->and($response['first_page'])->toBeString()
        ->and($response['last_page'])->toBeString()
        ->and($response['count'])->toBe(100)
        ->and($response['results'])->toBeArray();
});

it('can pay using different types of cards', function ($paymentDetails, $expectedResponse) {
    mockTokenRequest($this->client);

    // Ajuste en el Mock para manejar todos los argumentos correctamente
    $this->client->shouldReceive('request')
        ->with('POST', '/payment', Mockery::subset([
            'headers' => ['Authorization' => 'Bearer some-token'],
            'json' => $paymentDetails,
        ]))
        ->andReturn(new Response(200, [], json_encode($expectedResponse)));

    $result = $this->creditCardVe->payUsingCard($paymentDetails);

    expect($result)->toBeArray();

    if (isset($expectedResponse['financial_card_emitter'])) {
        expect($result['financial_card_emitter'])->toBeArray()
            ->and($result['card_status'])->toBe('LOCKED')
            ->and($result['otp_ccr_config'])->toBeArray();
    } else {
        expect($result['code'])->toBe(202)
            ->and($result['message'])->toBe('CREDICARD_RESPONSE_UNSUCCESSFUL')
            ->and($result['cause'])->toBeArray();
    }
})->with('payment cases');

it('successfully encrypts a PIN with a valid public key', function () {
    $pin = 1234;

    $publicKey = <<<'EOD'
    -----BEGIN PUBLIC KEY-----
    MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3f7n2wAObZAqnXi0XLyg
    UFxV21ExGUimDKTmtAIp3QLfqbywP0bY/lrHfBZsmmrjyXpy4Hrvok/ugmNZnVbV
    TBDG5YvUQmMESo4n8QJR0qnHmY4PfiJgN32/VXX1JeTNC3HESJTQRlmBHl05uh/b
    jGy7Pi1Gd0A9Ti2ANvZvGo667vEsRlYjbjK1/dBGS6pzgGsyRG8CJJZrApguLXob
    N25zzvmuY1kXPSG/9Nv7HpmgxhdkLhbmTBKA0U3pgiYXkcmv6TgKAIVr1ixeZu/d
    0fhJQSOYRg+ziJW9wz1/XoDTLIyCO/egXuhKci0YSPM8tArIiSwvODCCl+Cc0j/I
    XwIDAQAB
    -----END PUBLIC KEY-----
    EOD;

    $encryptedPin = $this->creditCardVe->encryptPin($pin, $publicKey);

    expect($encryptedPin)->not->toBeFalse()
        ->and($encryptedPin)->toBeString();
});

it('fails to encrypt with an invalid public key', function () {
    $pin = 1234;

    $invalidPublicKey = 'clave-publica-invalida';

    $encryptedPin = $this->creditCardVe->encryptPin($pin, $invalidPublicKey);

    expect($encryptedPin)->toBeFalse();
});

it('correctly formats a valid public key', function () {
    $rawKey = 'MIICIjANBgkqhkiG9w0...';

    $formattedKey = $this->creditCardVe->formatPublicKey($rawKey);

    expect($formattedKey)->toStartWith('-----BEGIN PUBLIC KEY-----')
        ->and($formattedKey)->toEndWith('-----END PUBLIC KEY-----')
        ->and($formattedKey)->toContain("\n");
});

it('adds missing BEGIN and END tags to a public key', function () {
    $rawKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3f7n2wAObZAqnXi0XLyg...';

    $formattedKey = $this->creditCardVe->formatPublicKey($rawKey);

    expect($formattedKey)->toStartWith('-----BEGIN PUBLIC KEY-----')
        ->and($formattedKey)->toEndWith('-----END PUBLIC KEY-----');
});
