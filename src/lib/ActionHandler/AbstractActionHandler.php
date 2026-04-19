<?php

declare(strict_types=1);

namespace ConnectorGemini\ActionHandler;

use Ibexa\Contracts\ConnectorAi\Action\ActionHandlerInterface;
use Ibexa\Contracts\ConnectorAi\ActionInterface;
use Ibexa\Contracts\ConnectorAi\DataType;
use Ibexa\Contracts\Core\Repository\LanguageResolver;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractActionHandler implements ActionHandlerInterface
{
    protected string $defaultModel = 'gemini-2.5-flash';

    protected int $defaultMaxTokens = 8192;

    protected float $defaultTemperature = 1.0;

    public function __construct(
        private readonly LanguageResolver $languageResolver,
        protected readonly HttpClientInterface $httpClient,
        protected readonly string $apiKey,
    ) {
    }

    abstract public static function getIdentifier(): string;

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('prompt')->allowedTypes('string');
        $resolver->define('action_type_identifier')
            ->required()
            ->default(null)
            ->allowedTypes('string');

        $resolver->define('action_handler_identifier')
            ->required()
            ->default(static::getIdentifier())
            ->allowedTypes('string');

        $resolver->define('languageCode')
            ->required()
            ->default($this->getDefaultLanguageCode())
            ->allowedTypes('null', 'string');

        $resolver->define('max_tokens')
            ->required()
            ->default($this->defaultMaxTokens)
            ->allowedTypes('int');

        $resolver->define('model')
            ->required()
            ->default($this->defaultModel)
            ->allowedTypes('string');

        $resolver->define('temperature')
            ->required()
            ->default($this->defaultTemperature)
            ->allowedTypes('numeric');

        $resolver->define('action_input')
            ->allowedTypes(DataType::class);
    }

    /**
     * @return array<mixed>
     */
    protected function resolveOptions(ActionInterface $action): array
    {
        $resolver = new OptionsResolver();

        $this->configureOptions($resolver);

        return $resolver->resolve($action->getAllOptions());
    }

    /**
     * @param array<mixed> $requestBody
     * @return array<mixed>
     */
    protected function callGeminiApi(string $model, array $requestBody): array
    {
        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            $model
        );

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ],
            'json' => $requestBody,
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            $body = $response->toArray(false);
            $message = $body['error']['message'] ?? 'Gemini API request failed with status ' . $statusCode;
            throw new RuntimeException($message);
        }

        return $response->toArray();
    }

    /**
     * @param array<mixed> $responseData
     */
    protected function extractTextFromResponse(array $responseData): string
    {
        $output = '';

        $candidates = $responseData['candidates'] ?? [];
        foreach ($candidates as $candidate) {
            $parts = $candidate['content']['parts'] ?? [];
            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    $output .= $part['text'];
                }
            }
        }

        if ($output === '') {
            throw new RuntimeException('Gemini returned no text content.');
        }

        return $output;
    }

    private function getDefaultLanguageCode(): string
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages();
        $defaultLanguageCode = reset($prioritizedLanguages);

        if ($defaultLanguageCode === false) {
            throw new RuntimeException('Unable to load default language code');
        }

        return $defaultLanguageCode;
    }

    public function setDefaultModel(string $defaultModel): void
    {
        $this->defaultModel = $defaultModel;
    }

    public function setDefaultMaxTokens(int $defaultMaxTokens): void
    {
        $this->defaultMaxTokens = $defaultMaxTokens;
    }

    public function setDefaultTemperature(float $defaultTemperature): void
    {
        $this->defaultTemperature = $defaultTemperature;
    }
}

