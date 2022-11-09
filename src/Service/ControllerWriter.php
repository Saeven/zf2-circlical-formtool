<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Service;

use Circlical\LaminasTools\Provider\WriterInterface;
use Laminas\Config\Config;
use Laminas\Config\Writer\PhpArray;
use RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function sprintf;

use const LOCK_EX;

final class ControllerWriter extends AbstractWriter implements WriterInterface
{
    public const RESOURCE_CONTROLLER = 'controller';
    public const RESOURCE_FACTORY = 'factory';

    private ?string $module;
    private ?string $controllerName;
    private ?bool $writeFactory;

    public function setOptions(array $options): void
    {
        $this->module = $options['module'];
        $this->controllerName = $options['controller'];
        $this->writeFactory = $options['writeFactory'];
    }

    /**
     * @return Array<?string>
     */
    public function write(OutputInterface $output): array
    {
        $table = new Table($output);
        $table->setStyle('box');
        $table->setHeaders(['Component', 'Status', 'Location']);

        $files = [
            $this->generateController($table),
            $this->generateFactory($table),
        ];

        $this->writeConfig($table);
        $table->render();

        return $files;
    }

    private function generateController(Table $table): ?string
    {
        $dir = Utilities::getSourceFolderForModule($this->module, ['Controller']);
        $controllerFile = $dir . "{$this->controllerName}Controller.php";

        if (is_file($controllerFile)) {
            $table->addRow(['Controller', '<fg=white;options=bold>exists</>', Utilities::modulePath($controllerFile)]);

            return null;
        }

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" could not be created; permissions issue?', $dir));
        }

        $template = Utilities::parseTemplate(
            $this->getResourceTemplate(self::RESOURCE_CONTROLLER),
            ['MODULE', 'CONTROLLER'],
            [$this->module, $this->controllerName]
        );
        file_put_contents($controllerFile, $template, LOCK_EX);
        $table->addRow(['Controller', '<fg=green;options=bold>created</>', Utilities::modulePath($controllerFile)]);

        return $controllerFile;
    }

    private function generateFactory(Table $table): ?string
    {
        if (!$this->writeFactory) {
            return null;
        }

        $dir = Utilities::getSourceFolderForModule($this->module, ['Factory', 'Controller']);
        $controllerFactoryFile = $dir . "{$this->controllerName}ControllerFactory.php";

        if (is_file($controllerFactoryFile)) {
            $table->addRow(['Factory', '<fg=white;options=bold>exists</>', Utilities::modulePath($controllerFactoryFile)]);

            return null;
        }

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" could not be created; permissions issue?', $dir));
        }

        $template = Utilities::parseTemplate(
            $this->getResourceTemplate(self::RESOURCE_FACTORY),
            ['MODULE', 'CONTROLLER'],
            [$this->module, $this->controllerName]
        );
        file_put_contents($dir . "/{$this->controllerName}ControllerFactory.php", $template, LOCK_EX);
        $table->addRow(['Factory', '<fg=green;options=bold>created</>', Utilities::modulePath($controllerFactoryFile)]);

        return $controllerFactoryFile;
    }

    private function writeConfig(Table $table): void
    {
        $configDirectory = sprintf(
            "%s%s/config/",
            Utilities::getModulesFolder(),
            $this->module
        );

        if (!@mkdir($configDirectory, 0755, true) && !is_dir($configDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" could not be created', $configDirectory));
        }

        $configFile = $configDirectory . "controllers.config.php";

        $configuration = [];
        $exists = false;
        if (is_file($configFile)) {
            $exists = true;
            $configuration = require $configFile;
        }
        $config = new Config($configuration, true);
        if (!$config->factories) {
            $config->factories = [];
        }

        $FQN = sprintf('\%s\Controller\%sController', $this->module, $this->controllerName);
        $factoryFQN = sprintf('\%s\Factory\Controller\%sControllerFactory', $this->module, $this->controllerName);
        $config->factories->$FQN = $factoryFQN;

        $writer = new PhpArray();
        $writer->setUseBracketArraySyntax(true);
        $writer->setUseClassNameScalars(true);
        $writer->toFile($configFile, $config, true);
        $table->addRow(['Configuration', '<fg=green;options=bold>' . ($exists ? 'adjusted' : 'created') . '</>', Utilities::modulePath($configFile)]);
    }
}
