<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Service;

use Laminas\Config\Config;
use Laminas\Config\Writer\PhpArray;

final class FormWriter
{
    private string $module;
    private string $form;
    private ?string $class;
    private bool $doctrine;

    public function __construct(string $module, string $form, bool $doctrine, ?string $class)
    {
        $this->module = $module;
        $this->form = $form;
        $this->class = $class;
        $this->doctrine = $doctrine;
    }

    public function write(): void
    {
        $this->generateForm();
        $this->generateFormFactory();
        $this->generateInputFilter();
        $this->generateInputFilterFactory();
        $this->writeConfig();
    }

    private function generateForm(): void
    {
        $dir = getcwd() . "/module/{$this->module}/src/Form";
        $template = file_get_contents(__DIR__ . '/../Resources/Form.txt');
        $template = str_replace(['MODULE', 'FORM'], [$this->module, $this->form], $template);

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        file_put_contents($dir . "/{$this->form}Form.php", $template, LOCK_EX);
    }

    private function generateFormFactory(): void
    {
        $dir = getcwd() . "/module/{$this->module}/src/Factory/Form";
        $template = file_get_contents(__DIR__ . '/../Resources/' . ($this->doctrine ? 'Doctrine' : '') . 'FormFactory.txt');
        $template = str_replace(
            [
                'HYDRATORFORM',
                'DHYDRATORUSE',
                'HYDRATORUSER',
                'MODULE',
                'FORM',
            ],
            [
                $this->class ? '$form->setObject( new ' . $this->class . '() );' : '',
                $this->class ? 'use ' . $this->module . '\\Entity\\' . $this->class . ';' : '',
                $this->class ? 'use ' . $this->module . '\\Model\\' . $this->class . ';' : '',
                $this->module,
                $this->form,

            ],
            $template
        );

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        file_put_contents($dir . "/{$this->form}FormFactory.php", $template, LOCK_EX);
    }

    private function generateInputFilter(): void
    {
        $dir = getcwd() . "/module/{$this->module}/src/InputFilter";
        $template = file_get_contents(__DIR__ . '/../Resources/InputFilter.txt');
        $template = str_replace(['MODULE', 'FORM'], [$this->module, $this->form], $template);

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        file_put_contents($dir . "/{$this->form}InputFilter.php", $template, LOCK_EX);
    }

    private function generateInputFilterFactory(): void
    {
        $dir = getcwd() . "/module/{$this->module}/src/Factory/InputFilter";
        $template = file_get_contents(__DIR__ . '/../Resources/InputFilterFactory.txt');
        $template = str_replace(['MODULE', 'FORM'], [$this->module, $this->form], $template);

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        file_put_contents($dir . "/{$this->form}InputFilterFactory.php", $template, LOCK_EX);
    }

    private function writeConfig(): void
    {
        //
        // Write Form
        //
        $formConfigDirectory = $formConfigFile = getcwd() . DIRECTORY_SEPARATOR . "module" . DIRECTORY_SEPARATOR .
            $this->module . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR;

        if (!@mkdir($formConfigDirectory, 0755, true) && !is_dir($formConfigDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" could not be created', $formConfigDirectory));
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

        $filterConfigFile = getcwd() . DIRECTORY_SEPARATOR . "module" . DIRECTORY_SEPARATOR .
            $this->module . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "inputfilters.config.php";

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

