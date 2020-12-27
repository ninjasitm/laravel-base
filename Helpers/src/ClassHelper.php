<?php

namespace Nitm\Helpers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class helper
 * @author https://stackoverflow.com/users/2039952/cylosh
 * @link https://stackoverflow.com/a/56995448
 */
class ClassHelper
{
    /**
     * @param string $namespace
     * 
     * @return array
     */
    public static function findRecursive(string $namespace): array
    {
        $namespacePath = self::translateNamespacePath($namespace);

        if ($namespacePath === '') {
            return [];
        }

        return self::searchClasses($namespace, $namespacePath);
    }

    /**
     * @param string $namespace
     * 
     * @return string
     */
    protected static function translateNamespacePath(string $namespace): string
    {
        $nsParts = explode('\\', $namespace);
        array_shift($nsParts);

        if (empty($nsParts)) {
            return '';
        }

        return app_path(implode(DIRECTORY_SEPARATOR, $nsParts)) ?: '';
    }

    /**
     * @param string $namespace
     * @param string $namespacePath
     * 
     * @return array
     */
    private static function searchClasses(string $namespace, string $namespacePath): array
    {
        $classes = [];

        /**
         * @var \RecursiveDirectoryIterator $iterator
         * @var \SplFileInfo $item
         */
        foreach ($iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($namespacePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $item) {
            if ($item->isDir()) {
                $nextPath = $iterator->current()->getPathname();
                $nextNamespace = $namespace . '\\' . $item->getFilename();
                $classes = array_merge($classes, self::searchClasses($nextNamespace, $nextPath));
                continue;
            }
            if ($item->isFile() && $item->getExtension() === 'php') {
                $class = $namespace . '\\' . $item->getBasename('.php');
                if (!class_exists($class)) {
                    continue;
                }
                $classes[] = $class;
            }
        }

        return $classes;
    }
}