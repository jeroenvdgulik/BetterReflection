<?php

namespace BetterReflection\SourceLocator\Type;

use BetterReflection\Identifier\Identifier;
use BetterReflection\Identifier\IdentifierType;
use BetterReflection\Reflector\Reflector;
use BetterReflection\SourceLocator\Exception\InvalidDirectory;

/**
 * This source locator loads all php files in an entire directories.
 */
class DirectorySourceLocator implements SourceLocator
{

    /**
     * @var AggregateSourceLocator
     */
    private $aggregatedSourceLocator;

    /**
     * @param $directories array directories to scan
     */
    public function __construct(array $directories)
    {
        $sourceLocators = [];
        foreach ($directories as $dir) {
            $dir = (string) $dir;
            if (!is_dir($dir)) {
                throw new InvalidDirectory(sprintf('Is not a directory: %s', $dir));
            }
            $sourceLocators = array_merge($sourceLocators, $this->scan($dir));
        }
        $this->aggregatedSourceLocator = new AggregateSourceLocator($sourceLocators);
    }

    /**
     * scan target directory and resulted as SourceLocator[]
     * @param $dir string directory path
     * @return SourceLocator[]
     */
    private function scan($dir) {
        $sourceLocators = [];
        $rdi = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        foreach ( new \RecursiveIteratorIterator($rdi) as $item) {
            if ($item->isFile() && pathinfo($item->getRealPath(), PATHINFO_EXTENSION) == 'php') {
                $sourceLocators[] = new SingleFileSourceLocator($item->getRealPath());
            }
        }
        return $sourceLocators;
    }

    /**
     * {@inheritDoc}
     */
    public function locateIdentifier(Reflector $reflector, Identifier $identifier)
    {
        return $this->aggregatedSourceLocator->locateIdentifier($reflector, $identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType)
    {
        return $this->aggregatedSourceLocator->locateIdentifiersByType($reflector, $identifierType);
    }
}