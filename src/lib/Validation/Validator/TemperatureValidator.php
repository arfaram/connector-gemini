<?php

declare(strict_types=1);

namespace ConnectorGemini\Validation\Validator;

use Ibexa\Contracts\ConnectorAi\ActionType\OptionsValidatorError;
use Ibexa\Contracts\ConnectorAi\ActionType\OptionsValidatorInterface;
use Ibexa\Contracts\Core\Options\OptionsBag;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

final class TemperatureValidator implements OptionsValidatorInterface, TranslationContainerInterface
{
    public const string MESSAGE = 'Temperature must be a number between 0 and 2.';

    public function validateOptions(OptionsBag $options): array
    {
        $temperature = $options->get('temperature');

        if (!is_numeric($temperature) || $temperature < 0 || $temperature > 2) {
            return [
                new OptionsValidatorError(
                    '[temperature]',
                    'ibexa.connector_gemini.action_configuration.temperature_validator'
                ),
            ];
        }

        return [];
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                'ibexa.connector_gemini.action_configuration.temperature_validator',
                'validators'
            )->setDesc(self::MESSAGE),
        ];
    }
}

