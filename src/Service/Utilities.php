<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Service;

use RuntimeException;

use function file_get_contents;
use function getcwd;
use function implode;
use function is_file;
use function sprintf;
use function str_replace;
use function strpos;
use function substr;

use const DIRECTORY_SEPARATOR;

final class Utilities
{
    private static ?string $modulesFolder = null;

    public static function getModulesFolder(): string
    {
        if (!self::$modulesFolder) {
            self::$modulesFolder = getcwd() . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR;
        }

        return self::$modulesFolder;
    }

    /**
     * Get yourself a path to some src, with trailing slash.
     */
    public static function getSourceFolderForModule(string $moduleName, ?array $subpaths = null): string
    {
        $folder = sprintf(
            "%s%s/src/",
            self::getModulesFolder(),
            $moduleName
        );

        if ($subpaths) {
            $folder .= implode("/", $subpaths) . "/";
        }

        return $folder;
    }

    public static function parseTemplate(string $file, array $search, array $replace): string
    {
        if (!is_file($file)) {
            throw new RuntimeException("A template file could not be found at: $file");
        }

        return str_replace(
            $search,
            $replace,
            file_get_contents($file)
        );
    }

    /**
     * Return what follows module/
     */
    public static function modulePath(string $path): string
    {
        return substr($path, strpos($path, 'module/'));
    }
}
