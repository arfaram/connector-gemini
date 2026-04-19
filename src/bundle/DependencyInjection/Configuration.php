<?php

declare(strict_types=1);

namespace ConnectorGeminiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public const array GEMINI_TEXT_MODELS = [
        'gemini-2.5-pro' => 'Gemini 2.5 Pro',
        'gemini-2.5-flash' => 'Gemini 2.5 Flash',
        'gemini-2.0-flash' => 'Gemini 2.0 Flash',
        'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash Lite',
    ];

    public const array GEMINI_VISION_MODELS = self::GEMINI_TEXT_MODELS;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder(ConnectorGeminiExtension::EXTENSION_NAME);

        $rootNode = $builder->getRootNode();
        $rootNode
            ->children()
                ->append($this->getActionConfigurationNode('text_to_text', self::GEMINI_TEXT_MODELS))
                ->append($this->getActionConfigurationNode('image_to_text', self::GEMINI_VISION_MODELS))
            ->end();

        return $builder;
    }

    /**
     * @param array<string, string> $models
     */
    private function getActionConfigurationNode(string $name, array $models): ArrayNodeDefinition
    {
        $builder = new TreeBuilder($name);

        $rootNode = $builder->getRootNode();
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('models')
                    ->defaultValue($models)
                ->end()
                ->scalarNode('default_model')
                    ->defaultValue('gemini-2.5-flash')
                    ->info('Default model identifier.')
                ->end()
                ->integerNode('default_max_tokens')
                    ->defaultValue(8192)
                    ->info('Default maximum number of tokens that can be generated.')
                ->end()
                ->floatNode('default_temperature')
                    ->defaultValue(1.0)
                    ->min(0.0)
                    ->max(2.0)
                    ->info('Default sampling temperature to use, between 0 and 2.')
                ->end()
            ->end();

        return $rootNode;
    }
}

