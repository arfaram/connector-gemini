<?php

declare(strict_types=1);

namespace ConnectorGemini\Validation\Validator;

use Ibexa\Contracts\ConnectorAi\ActionType\OptionsValidatorError;
use Ibexa\Contracts\ConnectorAi\ActionType\OptionsValidatorInterface;
use Ibexa\Contracts\Core\Options\OptionsBag;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

final class MaxTokensValidator implements OptionsValidatorInterface, TranslationContainerInterface
{
    public const int MAX_TOKENS = 65536;

    public const string MESSAGE = 'Max tokens must be greater than 0 and equal or lower than ' . self::MAX_TOKENS . '.';

    public function validateOptions(OptionsBag $options): array
    {
        $maxTokens = (int)$options->get('max_tokens');

        if ($maxTokens <= 0 || $maxTokens > self::MAX_TOKENS) {
            return [
                new OptionsValidatorError(
                    '[max_tokens]',
                    'ibexa.connector_gemini.action_configuration.max_tokens_validator'
                ),
            ];
        }

        return [];
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                'ibexa.connector_gemini.action_configuration.max_tokens_validator',
                'validators'
            )->setDesc(self::MESSAGE),
        ];
    }
}

