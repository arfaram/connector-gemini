<?php

declare(strict_types=1);

use Ibexa\Contracts\Rector\Factory\IbexaRectorConfigFactory;

return (new IbexaRectorConfigFactory(
    [
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]
))->createConfig();
