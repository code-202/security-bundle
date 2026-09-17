<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Attribute\SortAttributeNamedArgsRector;
use Rector\Config\RectorConfig;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets()
    ->withComposerBased(
        doctrine: true,
        symfony: true,
    )
    ->withAttributesSets(
        symfony: true,
        doctrine: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
        phpunitNarrowAsserts: true,
        phpunitMockToStub: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__ . '/src/Entity',
        ],
        SortAttributeNamedArgsRector::class,
        RemoveExtraParametersRector::class,
    ])
;
