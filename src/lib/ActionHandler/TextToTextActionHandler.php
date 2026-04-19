<?php

declare(strict_types=1);

namespace ConnectorGemini\ActionHandler;

use Ibexa\Contracts\ConnectorAi\Action\DataType\Text;
use Ibexa\Contracts\ConnectorAi\Action\Response\TextResponse;
use Ibexa\Contracts\ConnectorAi\Action\TextToText\Action as TextToTextAction;
use Ibexa\Contracts\ConnectorAi\ActionInterface;
use Ibexa\Contracts\ConnectorAi\ActionResponseInterface;
use Ibexa\Contracts\ConnectorAi\PromptResolverInterface;
use Ibexa\Contracts\Core\Repository\LanguageResolver;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TextToTextActionHandler extends AbstractActionHandler
{
    public const string INDEX = 'gemini-text-to-text';

    public function __construct(
        private readonly PromptResolverInterface $promptResolver,
        LanguageResolver $languageResolver,
        HttpClientInterface $httpClient,
        string $apiKey,
    ) {
        parent::__construct($languageResolver, $httpClient, $apiKey);
    }

    public function supports(ActionInterface $action): bool
    {
        return $action instanceof TextToTextAction;
    }

    public function handle(ActionInterface $action, array $context = []): ActionResponseInterface
    {
        if (!$action instanceof TextToTextAction) {
            throw new InvalidArgumentException('Action must be an instance of TextToTextAction.');
        }

        $options = $this->resolveOptions($action);
        $systemPrompt = $this->promptResolver->getPrompt($options);

        $requestBody = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $action->getInput()->getText()],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'],
                'maxOutputTokens' => $options['max_tokens'],
            ],
        ];

        if (!empty($systemPrompt)) {
            $requestBody['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ];
        }

        $model = $options['model'] ?? $this->defaultModel;
        $responseData = $this->callGeminiApi($model, $requestBody);
        $output = $this->extractTextFromResponse($responseData);

        return new TextResponse(new Text([$output]));
    }

    public static function getIdentifier(): string
    {
        return self::INDEX;
    }
}

