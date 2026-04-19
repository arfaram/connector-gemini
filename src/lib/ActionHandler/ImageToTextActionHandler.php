<?php

declare(strict_types=1);

namespace ConnectorGemini\ActionHandler;

use Ibexa\Contracts\ConnectorAi\Action\DataType\Text;
use Ibexa\Contracts\ConnectorAi\Action\ImageToText\Action as ImageToTextAction;
use Ibexa\Contracts\ConnectorAi\Action\Response\TextResponse;
use Ibexa\Contracts\ConnectorAi\ActionInterface;
use Ibexa\Contracts\ConnectorAi\ActionResponseInterface;
use Ibexa\Contracts\ConnectorAi\PromptResolverInterface;
use Ibexa\Contracts\Core\Repository\LanguageResolver;
use InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ImageToTextActionHandler extends AbstractActionHandler
{
    public const string INDEX = 'gemini-image-to-text';

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
        return $action instanceof ImageToTextAction;
    }

    public function handle(ActionInterface $action, array $context = []): ActionResponseInterface
    {
        if (!$action instanceof ImageToTextAction) {
            throw new InvalidArgumentException(sprintf(
                'Action must be an instance of %s, got: %s',
                ImageToTextAction::class,
                get_debug_type($action)
            ));
        }

        $options = $this->resolveOptions($action);
        $systemPrompt = $this->promptResolver->getPrompt($options);

        // Parse the data URI: data:<mimeType>;base64,<data>
        $base64Uri = $action->getInput()->getBase64();
        $mimeType = 'image/png';
        $imageData = $base64Uri;

        if (str_starts_with($base64Uri, 'data:')) {
            if (preg_match('#^data:([^;]+);base64,(.+)$#', $base64Uri, $matches)) {
                $mimeType = $matches[1];
                $imageData = $matches[2];
            }
        }

        $requestBody = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $imageData,
                            ],
                        ],
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

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->define('max_length')->allowedTypes('int');
    }
}

