[![GitHub tag (latest SemVer)](https://img.shields.io/github/v/tag/arfaram/connector-gemini?style=flat-square&color=blue)](https://github.com/arfaram/connector-gemini/tags)
[![Downloads](https://img.shields.io/packagist/dt/arfaram/connector-gemini.svg?style=flat-square&color=blue)](https://packagist.org/packages/arfaram/connector-gemini)
[![License](https://img.shields.io/packagist/l/arfaram/connector-gemini.svg?style=flat-square&color=blue)](https://github.com/arfaram/connector-gemini/blob/master/LICENSE)

# connector-gemini

Google Gemini AI connector for [Ibexa DXP](https://ibexa.co).

This bundle integrates Google Gemini API with the Ibexa AI framework (`ibexa/connector-ai`), providing action handlers for text-to-text and image-to-text AI operations.

## Requirements

- PHP >= 8.3
- Ibexa DXP ~5.0
- `ibexa/connector-ai` ~5.0
- Google Gemini API key

## Installation

Require the package:

```bash
composer require arfaram/connector-gemini
```

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    ConnectorGeminiBundle\ConnectorGeminiBundle::class => ['all' => true],
];
```

## Environment Variable

Set your Gemini API key in `.env`:

```dotenv
GEMINI_API_KEY=your-google-gemini-api-key
```
See: https://aistudio.google.com/api-keys

## Configuration

### Available Gemini Models (latest 4)

| Model ID | Description | Max Output Tokens | Input |
|---|---|---|---|
| `gemini-2.5-pro` | Most capable model, best for complex reasoning and coding | 65,536 | Text, Image, Audio, Video |
| `gemini-2.5-flash` | Best balance of speed, quality and cost | 65,536 | Text, Image, Audio, Video |
| `gemini-2.0-flash` | Fast, versatile, next-gen features | 8,192 | Text, Image, Audio, Video |
| `gemini-2.0-flash-lite` | Fastest, most cost-efficient | 8,192 | Text, Image, Audio, Video |

### Basic Configuration

Create `config/packages/ibexa_connector_gemini.yaml`:

```yaml
ibexa_connector_gemini:
    text_to_text:
        default_model: gemini-2.5-flash
        default_temperature: 1.0
        default_max_tokens: 8192
        models:
            gemini-2.5-pro: 'Gemini 2.5 Pro'
            gemini-2.5-flash: 'Gemini 2.5 Flash'
            gemini-2.0-flash: 'Gemini 2.0 Flash'
            gemini-2.0-flash-lite: 'Gemini 2.0 Flash Lite'
    image_to_text:
        default_model: gemini-2.5-flash
        default_temperature: 1.0
        default_max_tokens: 8192
        models:
            gemini-2.5-pro: 'Gemini 2.5 Pro'
            gemini-2.5-flash: 'Gemini 2.5 Flash'
            gemini-2.0-flash: 'Gemini 2.0 Flash'
            gemini-2.0-flash-lite: 'Gemini 2.0 Flash Lite'
```

### SiteAccess-Aware API Key Configuration

You can configure the API key per SiteAccess in `config/packages/ibexa.yaml`:

```yaml
ibexa:
    system:
        default:
            connector_gemini:
                gemini:
                    api_key: '%env(GEMINI_API_KEY)%'
        my_siteaccess:
            connector_gemini:
                gemini:
                    api_key: '%env(GEMINI_API_KEY_MY_SITEACCESS)%'
```

### Minimal Configuration (uses defaults)

If you only want to override the default model:

```yaml
ibexa_connector_gemini:
    text_to_text:
        default_model: gemini-2.5-pro
```

All other values will use their defaults:
- `default_temperature`: `1.0`
- `default_max_tokens`: `8192`
- `models`: all 4 latest Gemini models

### Production-Optimized Configuration

For production environments favoring speed and cost:

```yaml
ibexa_connector_gemini:
    text_to_text:
        default_model: gemini-2.5-flash
        default_temperature: 0.7
        default_max_tokens: 4096
        models:
            gemini-2.5-flash: 'Gemini 2.5 Flash'
            gemini-2.0-flash-lite: 'Gemini 2.0 Flash Lite'
    image_to_text:
        default_model: gemini-2.5-flash
        default_temperature: 0.5
        default_max_tokens: 2048
        models:
            gemini-2.5-flash: 'Gemini 2.5 Flash'
            gemini-2.0-flash: 'Gemini 2.0 Flash'
```

## License

MIT - See [LICENSE](LICENSE) for details.

## Author

Ramzi Arfaoui - ramzi_arfa@hotmail.de
