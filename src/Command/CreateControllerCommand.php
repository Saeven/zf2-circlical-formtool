<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Command;

use Circlical\LaminasTools\Service\ControllerWriter;
use Circlical\LaminasTools\Service\Utilities;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateControllerCommand extends AbstractCommand
{
    protected static $defaultName = "ct:create-controller";

    protected function configure()
    {
        $this->setName('create-controller');
        $this->setDescription("Create and wire a Laminas Controller & Factory.");
        $this->setHelp("Wires a controller for you, and optionally generates a Factory for it as well.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modulePath = Utilities::getModulesFolder();

        $helper = $this->getHelper('question');
        $output->writeln("<fg=white;options=bold>Controller Generation Guide</>");

        //
        // 1. Which module?
        //
        $moduleQuestion = new Question("<fg=green;options=bold>(1/3) In which laminas-mvc module should your controller be created? </>");
        $moduleQuestion->setAutocompleterCallback(function (string $userInput) use ($modulePath): array {
            $moduleSearch = $modulePath . '*';

            return array_map(static function (string $value) use ($modulePath) {
                return str_replace($modulePath, '', $value);
            }, glob($moduleSearch, GLOB_ONLYDIR));
        });
        $moduleQuestion->setValidator(function (?string $answer) use ($modulePath) {
            if (!$answer) {
                throw new \RuntimeException('A laminas-mvc module name is required.');
            }

            if (!is_dir($modulePath . $answer)) {
                throw new \RuntimeException(sprintf("The module with name '%s' could not be found.", $answer));
            }

            return $answer;
        });

        if (!$module = $helper->ask($input, $output, $moduleQuestion)) {
            throw new \RuntimeException("A module name is required.");
        }

        //
        // 2. What controller?
        //
        $controllerQuestion = new Question("<fg=green;options=bold>(2/3) What is your controller named? We will add the 'Controller' suffix automatically: </>");
        $controllerQuestion->setValidator(function (?string $answer) {
            if (!$answer) {
                throw new \RuntimeException(
                    "A controller name is required."
                );
            }

            if (stripos($answer, 'Controller') !== false) {
                throw new \RuntimeException(
                    "Don't include the word 'Controller' in your form name, I'll take care of that."
                );
            }

            return $answer;
        });

        if (!$controllerName = $helper->ask($input, $output, $controllerQuestion)) {
            throw new \RuntimeException("A valid controller name is required.");
        }

        //
        // 3. Doctrine Entity?
        //
        $factoryQuestion = new Question("<fg=green;options=bold>(3/3) Do you want to write a factory as well? (y/N) </>");
        $factoryQuestion->setValidator(function (?string $answer) {
            $answer = strtolower(trim($answer ?? ''));
            if (!$answer || $answer === 'n' || $answer === 'no') {
                return false;
            }

            if ($answer === 'y' || $answer === 'yes') {
                return true;
            }

            throw new \RuntimeException("Hm? Not sure I got that. Please answer yes or no.");
        });

        $factory = $helper->ask($input, $output, $factoryQuestion);

        $this->writer->setOptions([
            'module' => $module,
            'controller' => $controllerName,
            'writeFactory' => $factory,
        ]);

        $this->openFiles($this->writer->write($output));

        return Command::SUCCESS;
    }

    public static function getWriterService(): string
    {
        return ControllerWriter::class;
    }
}
