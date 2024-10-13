
# CrediCardVe API Integration

Esta clase permite interactuar con los endpoints de la API de Credicard para procesar pagos, consultar información de tarjetas y emitir tokens de validación bancaria.

## Instalación

- Instancia la clase `CrediCardVe` proporcionando el `clientId`, `clientSecret` y `baseUrl`.


```php
use CrediCardVe\CrediCardVe\CrediCardVe;

$clientId = 'tu-client-id';
$clientSecret = 'tu-client-secret';
$baseUrl = 'https://api.example.com';

$crediCardVe = new CrediCardVe($clientId, $clientSecret, $baseUrl);
```

> [!TIP]
> Se puede pasar un cuarto argumento: `false` para deshabilitar el checkeo SSL al momento de hacer la peticiones HTTP.

## Métodos Disponibles

### 1. `getCardBankInfo(int $cardNumber): mixed`

#### Descripción:
Este método obtiene la información bancaria asociada a una tarjeta de crédito o débito.

#### Ejemplo de uso:

```php
$cardNumber = 1234567890123456;
$response = $crediCardVe->getCardBankInfo($cardNumber);

print_r($response);
```

#### Respuesta esperada:

```json
{
	"bank_info": {
		"country": "VE",
		"code": "0172",
		"name": "BANCAMIGA, BANCO MICROFINANCIERO C.A.",
		"acronym": "BANCAMIGA",
		"thumbnail": "images/thumbnails/issuingBank/bancamiga.png",
		"no_bin_found_card_payment_allowed": true,
		"tdd_pin_required": true,
		"card_validation_config": {
			"TDD": "NONE"
		},
		"bank_card_validation": {
			"TDD": false
		}
	},
	"financial_card_emitter": {
		"name": "MAESTRO",
		"thumbnail": "images/issuingcard/maestro.png"
	},
	"card_status": "VALIDATED",
	"otp_ccr_config": {
		"enabled": true,
		"code_min_integer": 15,
		"code_max_integer": 40,
		"code_expiration_time": 24,
		"code_expiration_time_unit": "HOURS",
		"validation_expires_at_time": 180,
		"validation_expires_at_time_unit": "DAYS"
	}
}
```

---

### 2. `getCardHolderCommission(int $cardNumber, string $cardType, string $currency, float $amount): mixed`

#### Descripción:
Este método calcula la comisión asociada al tarjetahabiente para una transacción en particular.

#### Ejemplo de uso:

```php
$cardNumber = 1234567890123456;
$cardType = 'TDC'; // Tarjeta de crédito
$currency = 'USD';
$amount = 100.0;

$response = $crediCardVe->getCardHolderCommission($cardNumber, $cardType, $currency, $amount);

print_r($response);
```

#### Respuesta esperada:

```json
{
    "commission_amount": 2.5,
    "currency": "USD"
}
```

---

### 3. `payUsingCard(array $paymentData): mixed`

#### Descripción:
Procesa un pago utilizando los datos de una tarjeta de débito o crédito. 

#### Ejemplo de uso:

```php

$paymentData = [
    'currency' => 'VED',
    'amount' => 1.0,
    'reason' => 'PRUEBA',
    'country' => 'VE',
    'payer_name' => 'DHARRYLX',
    'debit_card' => [
        'holder_name' => 'DHARRYLX',
        'holder_id' => 'V016673906',
        'holder_id_doc' => 'RIF',
        'card_number' => '5859480000000146871',
        'cvc' => '941',
        'expiration_month' => 6,
        'expiration_year' => 24,
        'card_type' => 'DEBIT',
        'account_type' => 'CORRIENTE',
    ]
];

$response = $crediCardVe->payUsingCard($paymentData);

print_r($response);
```

### 3.1 Cifrar pin.

El PIN se encripta utilizando el método `encryptPin`. Solo se debe implementar cuando la consulta a `getCardBankInfo` retorne la key: `tdd_pin_required` como `true`.

#### Ejemplo de uso:

```php
$publicKey = '-----BEGIN PUBLIC KEY-----...-----END PUBLIC KEY-----';

$paymentData = [
    'currency' => 'VED',
    'amount' => 1.0,
    'reason' => 'PRUEBA',
    'country' => 'VE',
    'payer_name' => 'DHARRYLX',
    'debit_card' => [
        'holder_name' => 'DHARRYLX',
        'holder_id' => 'V016673906',
        'holder_id_doc' => 'RIF',
        'card_number' => '5859480000000146871',
        'cvc' => '941',
        'expiration_month' => 6,
        'expiration_year' => 24,
        'card_type' => 'DEBIT',
        'account_type' => 'CORRIENTE',
        'pin' => $crediCardVe->encryptPin(1234, $publicKey)
    ]
];

$response = $crediCardVe->payUsingCard($paymentData);

print_r($response);
```

> [!NOTE]
> De forma interna, al momento de pasar `$publicKey` se valida el formato de la llave.

#### Respuesta esperada:

```json
{
    "code": 200,
    "message": "Payment processed successfully",
    "transaction_id": "12345"
}
```

---

### 4. `bankCardSendToken(string $bankCode, string $rif, string $phone): mixed`

#### Descripción:
Envía un token de validación bancaria al cliente a través de su banco.

#### Ejemplo de uso:

```php
$bankCode = '0102';
$rif = 'V016673906';
$phone = '4241111111';

$response = $crediCardVe->bankCardSendToken($bankCode, $rif, $phone);

print_r($response);
```

#### Respuesta esperada:

```json
{
    "code": 200,
    "message": "BANK_CARD_VALIDATION_TOKEN_SENT"
}
```

---

### 5. `sendBankCardValidationToken(array $creditCardData): mixed`

#### Descripción:
Envía un token de validación para una tarjeta de crédito a través del servicio de Credicard.

#### Ejemplo de uso:

```php
$creditCardData = [
    'holder_name' => 'DHARRYLX',
    'card_number' => '4222610122997125',
    'holder_id' => 'V004000004',
    'holder_id_doc' => 'RIF',
    'expiration_month' => 12,
    'expiration_year' => 24,
    'cvc' => '808',
    'currency' => 'USD',
    'card_type' => 'CREDIT'
];

$response = $crediCardVe->sendBankCardValidationToken($creditCardData);

print_r($response);
```

#### Respuesta esperada:

```json
{
    "code": 200,
    "message": "NO_OTP_NEEDED"
}
```

---

### 6. `encryptPin(int $pin, string $publicKey): bool|string`

#### Descripción:
Este método encripta un PIN usando una clave pública proporcionada.

#### Ejemplo de uso:

```php
$pin = 1234;
$publicKey = '-----BEGIN PUBLIC KEY-----...-----END PUBLIC KEY-----';

$encryptedPin = $crediCardVe->encryptPin($pin, $publicKey);

echo $encryptedPin;
```

#### Respuesta esperada:

```text
XyZ123ABC...
```

---

### 7. `transactionReports(array $params): mixed`

#### Descripción:
Consulta el reporte de transacciones asociadas a una afiliación.

#### Ejemplo de uso:

```php
$params = [
    'begin' => '2023-01-01',
    'end' => '2023-01-31',
    'time_zone' => 'America/Caracas',
    'affiliation' => '10000000',
    'status_id' => 'PAY',
    'status' => 'APPROVED'
];

$response = $crediCardVe->transactionReports($params);

print_r($response);
```

#### Respuesta esperada:

```json
{
    "first_page": "offset=0&limit=10000",
    "last_page": "offset=0&limit=10000",
    "count": 100,
    "results": []
}
```
