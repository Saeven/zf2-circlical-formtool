<?php

namespace CirclicalFormTool\Controller;

use Laminas\Config\Config;
use Laminas\Config\Writer\PhpArray;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Console\Exception\RuntimeException;

class CliController extends AbstractActionController
{
    private ?string $module;
    private ?string $form;
    private ?bool $doctrine;
    private ?string $class;

    public const C_FAILURE = "\033[41m";
    public const C_SUCCESS = "\033[1;32m";
    public const C_RESET = "\033[0m";
    public const C_CYAN = "\033[0;36m";

    public function createFormAction(): string
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $this->module = ucfirst($this->params()->fromRoute('module'));
        $this->form = ucfirst($this->params()->fromRoute('form'));
        $this->doctrine = $this->params()->fromRoute('doctrine');
        $this->class = ucfirst($this->params()->fromRoute('class'));

        if (empty($this->module)) {
            return self::C_FAILURE . "You must specify the module in which the form is configured." . self::C_RESET . "\n";
        }

        if (empty($this->form)) {
            return self::C_FAILURE . "Please give your form a name\n";
        }

        if (stripos($this->form, 'Form') !== false) {
            return self::C_FAILURE . "Don't include the word 'Form' in your form name, I'll take care of that." . self::C_RESET . "\n";
        }

        if (!file_exists(getcwd() . '/module/' . $this->module)) {
            return self::C_FAILURE . "The module {$this->module} doesn't exist!" . self::C_RESET . "\n";
        }

        $files_created = [
            'Form/' . $this->form . 'Form.php',
            'Factory/Form/' . $this->form . 'FormFactory.php',
            'InputFilter' . $this->form . 'InputFilter.php',
            'Factory/InputFilter/' . $this->form . 'InputFilterFactory.php',
        ];

        $base = getcwd() . "/module/{$this->module}/src/{$this->module}/";
        foreach ($files_created as $f) {
            if (file_exists($base . $f)) {
                return self::C_FAILURE . "Sorry! $f already exists in module {$this->module}!" . self::C_RESET . "\n";
            }
        }

        $this->generateForm();
        $this->generateFormFactory();
        $this->generateInputFilter();
        $this->generateInputFilterFactory();

        //
        // Write Form
        //

        $formConfigFile = getcwd() . DIRECTORY_SEPARATOR . "module" . DIRECTORY_SEPARATOR .
            $this->module . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "forms.config.php";

        $configuration = [];
        if (file_exists($formConfigFile)) {
            $configuration = require $formConfigFile;
        }
        $config = new Config($configuration, true);
        if (!$config->factories) {
            $config->factories = [];
        }

        $formFQN = sprintf('%s\\Form\\%sForm', $this->module, $this->form);
        $formFactoryFQN = sprintf('%s\\Factory\\Form\\%sFormFactory', $this->module, $this->form);
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
        if (file_exists($filterConfigFile)) {
            $filterConfiguration = require $filterConfigFile;
        }
        $config = new Config($filterConfiguration, true);
        if (!$config->factories) {
            $config->factories = [];
        }

        $filterFQN = sprintf('%s\\InputFilter\\%sInputFilter', $this->module, $this->form);
        $filterFactoryFQN = sprintf('%s\\Factory\\InputFilter\\%sInputFilterFactory', $this->module, $this->form);
        $config->factories->$filterFQN = $filterFactoryFQN;

        $writer->toFile($filterConfigFile, $config, true);
        $str = self::C_SUCCESS . 'Success! Your form configurations have been successfully edited.' . self::C_RESET . "\n";

        return $str;
    }

    private function generateForm(): void
    {
        $dir = getcwd() . "/module/{$this->module}/src/{$this->module}/Form";
        $template = file_get_contents(__DIR__ . '/../Resources/Form.txt');
        $template = str_replace(['MODULE', 'FORM'], [$this->module, $this->form], $template);

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        file_put_contents($dir . "/{$this->form}Form.php", $template, LOCK_EX);
    }

    private function generateFormFactory(): void
    {
        $dir = getcwd() . "/module/{$this->module}/src/{$this->module}/Factory/Form";
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
        $dir = getcwd() . "/module/{$this->module}/src/{$this->module}/InputFilter";
        $template = file_get_contents(__DIR__ . '/../Resources/InputFilter.txt');
        $template = str_replace(['MODULE', 'FORM'], [$this->module, $this->form], $template);

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        file_put_contents($dir . "/{$this->form}InputFilter.php", $template, LOCK_EX);
    }

    private function generateInputFilterFactory(): void
    {
        $dir = getcwd() . "/module/{$this->module}/src/{$this->module}/Factory/InputFilter";
        $template = file_get_contents(__DIR__ . '/../Resources/InputFilterFactory.txt');
        $template = str_replace(['MODULE', 'FORM'], [$this->module, $this->form], $template);

        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        file_put_contents($dir . "/{$this->form}InputFilterFactory.php", $template, LOCK_EX);
    }
}
