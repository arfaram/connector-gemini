<?php

declare(strict_types=1);

namespace ConnectorGeminiBundle;

use ConnectorGeminiBundle\DependencyInjection\Configuration\SiteAccessAware\ConnectorGeminiParser;
use ConnectorGeminiBundle\DependencyInjection\ConnectorGeminiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ConnectorGeminiBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new ConnectorGeminiExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        /** @var \Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension $ibexaExtension */
        $ibexaExtension = $container->getExtension('ibexa');
        $ibexaExtension->addConfigParser(new ConnectorGeminiParser());
        $ibexaExtension->addDefaultSettings(__DIR__ . '/Resources/config', ['default_settings.yaml']);
    }
}

