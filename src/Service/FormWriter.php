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

final class FormWriter extends AbstractWriter implements WriterInterface
{
    public const RESOURCE_FORM = 'form';
    public const RESOURCE_FACTORY = 'factory';
    public const RESOURCE_FACTORY_DOCTRINE = 'doctrinefactory';
    public const RESOURCE_FILTER = 'inputfilter';
    public const RESOURCE_FILTER_FACTORY = 'inputfilterfactory';

    private string $module;
    private string $form;
    private ?string $hydrateClass;
    private bool $doctrine;

    public function setOptions(array $options): void
    {
        $this->module = $options['module'];
        $this->form = $options['form'];
        $this->hydrateClass = $options['hydrateClass'];
        $this->doctrine = $options['useDoctrine'];
    }

    public function write(OutputInterface $output): array
    {
        $table = new Table($output);
        $table->setStyle('box');
        $table->setHeaders(['Component', 'Status', 'Location']);

        $files = [
            $this->generateForm($table),
            $this->generateFormFactory($table),
            $this->generateInputFilter($table),
            $this->generateInputFilterFactory($table),
        ];
        $this->writeConfig();
        $table->render();

        return $files;
    }

    private function generateForm(Table $table): string
    {
        $dir = Utilities::getSourceFolderForModule($this->module, ['Form']);
        $formFile = $dir . "{$this->form}Form.php";

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" could not be created; permissions issue?', $dir));
        }

        $template = Utilities::parseTemplate(
            $this->getResourceTemplate(self::RESOURCE_FORM),
            ['MODULE', 'FORM'],
            [$this->module, $this->form]
        );
        file_put_contents($formFile, $template, LOCK_EX);
        $table->addRow(['Form', '<fg=green;options=bold>created</>', Utilities::modulePath($formFile)]);

        return $formFile;
    }

    private function generateFormFactory(Table $table): string
    {
        $dir = Utilities::getSourceFolderForModule($this->module, ['Factory', 'Form']);
        $formFactoryFile = $dir . "{$this->form}FormFactory.php";

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        $template = Utilities::parseTemplate(
            $this->getResourceTemplate(
                $this->doctrine ? self::RESOURCE_FACTORY_DOCTRINE : self::RESOURCE_FACTORY
            ),
            [
                'HYDRATORFORM',
                'DHYDRATORUSE',
                'HYDRATORUSE',
                'MODULE',
                'FORM',
            ],
            [
                $this->hydrateClass ? '$form->setObject( new ' . $this->hydrateClass . '() );' : '',
                $this->hydrateClass ? 'use ' . $this->module . '\\Entity\\' . $this->hydrateClass . ';' : '',
                $this->hydrateClass ? 'use ' . $this->module . '\\Model\\' . $this->hydrateClass . ';' : '',
                $this->module,
                $this->form,
            ],
        );

        file_put_contents($formFactoryFile, $template, LOCK_EX);
        $table->addRow(['Form Factory', '<fg=green;options=bold>created</>', Utilities::modulePath($formFactoryFile)]);

        return $formFactoryFile;
    }

    private function generateInputFilter(Table $table): string
    {
        $dir = Utilities::getSourceFolderForModule($this->module, ['InputFilter']);
        $inputFilterFile = $dir . "{$this->form}InputFilter.php";

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        $template = Utilities::parseTemplate(
            $this->getResourceTemplate(self::RESOURCE_FILTER),
            ['MODULE', 'FORM'],
            [$this->module, $this->form]
        );
        file_put_contents($inputFilterFile, $template, LOCK_EX);
        $table->addRow(['InputFilter', '<fg=green;options=bold>created</>', Utilities::modulePath($inputFilterFile)]);

        return $inputFilterFile;
    }

    private function generateInputFilterFactory(Table $table): string
    {
        $dir = Utilities::getSourceFolderForModule($this->module, ['Factory', 'InputFilter']);
        $inputFilterFactoryFile = $dir . "{$this->form}InputFilterFactory.php";

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        $template = Utilities::parseTemplate(
            $this->getResourceTemplate(self::RESOURCE_FILTER_FACTORY),
            [
                'MODULE',
                'FORM',
            ],
            [
                $this->module,
                $this->form,
            ]
        );
        file_put_contents($inputFilterFactoryFile, $template, LOCK_EX);
        $table->addRow(['InputFilter Factory', '<fg=green;options=bold>created</>', Utilities::modulePath($inputFilterFactoryFile)]);

        return $inputFilterFactoryFile;
    }

    private function writeConfig(): void
    {
        //
        // Write Form
        //
        $formConfigDirectory = sprintf(
            "%s%s/config/",
            Utilities::getModulesFolder(),
            $this->module
        );

        if (!@mkdir($formConfigDirectory, 0755, true) && !is_dir($formConfigDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" could not be created', $formConfigDirectory));
        }

        $formConfigFile = $formConfigDirectory . "forms.config.php";

        $configuration = [];
        if (is_file($formConfigFile)) {
            $configuration = require $formConfigFile;
        }
        $config = new Config($configuration, true);
        if (!$config->factories) {
            $config->factories = [];
        }

        $formFQN = sprintf('\%s\Form\%sForm', $this->module, $this->form);
        $formFactoryFQN = sprintf('\%s\Factory\Form\%sFormFactory', $this->module, $this->form);
        $config->factories->$formFQN = $formFactoryFQN;

        $writer = new PhpArray();
        $writer->setUseBracketArraySyntax(true);
        $writer->setUseClassNameScalars(true);
        $writer->toFile($formConfigFile, $config, true);

        //
        // Write InputFilter
        //

        $filterConfigFile = $formConfigDirectory . "inputfilters.config.php";

        $filterConfiguration = [];
        if (is_file($filterConfigFile)) {
            $filterConfiguration = require $filterConfigFile;
        }
        $config = new Config($filterConfiguration, true);
        if (!$config->factories) {
            $config->factories = [];
        }

        $filterFQN = sprintf('\%s\InputFilter\%sInputFilter', $this->module, $this->form);
        $filterFactoryFQN = sprintf('\%s\Factory\InputFilter\%sInputFilterFactory', $this->module, $this->form);
        $config->factories->$filterFQN = $filterFactoryFQN;

        $writer->toFile($filterConfigFile, $config, true);
    }
}
