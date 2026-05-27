<?php

/**
 * Rector bootstrap.
 *
 * @package EightshiftUtils
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
	->withPaths([__DIR__ . '/src'])
	->withBootstrapFiles([__DIR__ . '/vendor-prefixed/autoload.php'])
	->withPhpSets(php84: true)
	->withSets([LevelSetList::UP_TO_PHP_84, SetList::CODE_QUALITY, SetList::DEAD_CODE, SetList::TYPE_DECLARATION, SetList::EARLY_RETURN])
	->withIndent("\t", indentSize: 1)
	->withImportNames(importShortClasses: false, removeUnusedImports: true)
	->withParallel();
