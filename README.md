# This is my package creditcard-ve

[![Latest Version on Packagist](https://img.shields.io/packagist/v/abr4xas/creditcard-ve.svg?style=flat-square)](https://packagist.org/packages/abr4xas/credicard-ve)
[![Tests](https://img.shields.io/github/actions/workflow/status/abr4xas/creditcard-ve/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/abr4xas/credicard-ve/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/abr4xas/creditcard-ve.svg?style=flat-square)](https://packagist.org/packages/abr4xas/credicard-ve)

Es una interfaz para interactuar con la API de CrediCard. Proporciona métodos para obtener información sobre tarjetas bancarias y realizar pagos utilizando tarjetas de crédito o débito. Esta clase es útil para aplicaciones que necesitan procesar pagos de manera segura y eficiente utilizando la API de CrediCard.

## Installation

You can install the package via composer:

```bash
composer require abr4xas/credicard-ve
```

## Usage

```php
$crediCard = new CrediCardVe(
    'test-client-id',
    'test-client-secret',
    'https://api.example.com'
);

// todo
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Angel](https://github.com/abr4xas)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
