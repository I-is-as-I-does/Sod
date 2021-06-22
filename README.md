
# Sod

Encryption util; two flavours: Sodium (preferred), or Sugar.

## Getting Started

```bash
composer require ssitu/sod
```

## How to

```php
use SSITU\Sod\Sod;

require_once '/path/to/vendor/autoload.php';

// Sod config:
$sodConfig["cryptKey"] = '703af4dd03ebe11e35167157a8a697d8a2cb545a907a38289f8a7ba19432a342';
$sodConfig["flavour"] = "Sugar"; # prefer "Sodium" if installed

// Sod init:
$Sod = new Sod($sodConfig);
// or:
# $Sod->setCryptKey(string $key);
# $Sod->setFlavour(string $flavour);

// For a quick check:
$Sod->hasCryptKey();

// To test if Sodium is installed:
var_dump($Sod->isLibSodiumOn());

// Encrypt:
$Sod->encrypt(string $message);

// Decrypt:
$Sod->decrypt(string $message);

// If something went wrong:
$Sod->getLogs();
```

## Contributing

Sure! You can take a loot at [CONTRIBUTING](CONTRIBUTING.md).

## License

This project is under the MIT License; cf. [LICENSE](LICENSE) for details.