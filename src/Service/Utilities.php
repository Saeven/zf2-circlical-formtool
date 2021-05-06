<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Service;

final class Utilities
{
    private static ?string $modulesFolder = null;


    public static function getModulesFolder(): string
    {
        if (!static::$modulesFolder) {
            static::$modulesFolder = getcwd() . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR;
        }

        return static::$modulesFolder;
    }


    /**
     * Get yourself a path to some src, with trailing slash.
     */
    public static function getSourceFolderForModule(string $moduleName, ?array $subpaths = null): string
    {
        $folder = sprintf(
            "%s%s/src/",
            static::getModulesFolder(),
            $moduleName
        );

        if ($subpaths) {
            $folder .= implode("/", $subpaths) . "/";
        }

        return $folder;
    }


    public static function parseTemplate(string $name, array $search, array $replace): string
    {
        return str_replace($search, $replace, file_get_contents(__DIR__ . '/../Resources/' . $name . '.txt'));
    }


    /**
     * Return what follows module/
     */
    public static function modulePath(string $path): string
    {
        return substr($path, strpos($path, 'module/'));
    }
}
