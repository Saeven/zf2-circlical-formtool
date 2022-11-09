<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Command;

use Circlical\LaminasTools\Service\FormWriter;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function array_map;
use function file_exists;
use function getcwd;
use function glob;
use function is_dir;
use function is_file;
use function sprintf;
use function str_replace;
use function stripos;
use function strtolower;
use function trim;

use const DIRECTORY_SEPARATOR;
use const GLOB_ONLYDIR;

class CreateFormCommand extends AbstractCommand
{
    /** @var string */
    protected static $defaultName = "ct:create-form";

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modulePath = $this->getModulePath();

        $helper = $this->getHelper('question');
        $output->writeln("\n<fg=white;options=bold>Form Generation Guide</>");

        //
        // 1. Which module?
        //
        $moduleQuestion = new Question("<fg=green;options=bold>(1/4) In which laminas-mvc module should your form be created? </>");
        $moduleQuestion->setAutocompleterCallback(function (string $userInput) use ($modulePath): array {
            $moduleSearch = $modulePath . '*';

            return array_map(static function (string $value) use ($modulePath) {
                return str_replace($modulePath, '', $value);
            }, glob($moduleSearch, GLOB_ONLYDIR));
        });
        $moduleQuestion->setValidator(function (?string $answer) use ($modulePath) {
            if (!$answer) {
                throw new RuntimeException('A laminas-mvc module name is required.');
            }
            if (!is_dir($modulePath . $answer)) {
                throw new RuntimeException(sprintf("The module with name '%s' could not be found.", $answer));
            }

            return $answer;
        });

        if (!$module = $helper->ask($input, $output, $moduleQuestion)) {
            throw new RuntimeException("A module name is required.");
        }

        //
        // 2. What form?
        //
        $formQuestion = new Question("<fg=green;options=bold>(2/4) What is your form named? We will add the 'Form' suffix automatically: </>");
        $formQuestion->setValidator(function (?string $answer) use ($modulePath) {
            if (!$answer) {
                throw new RuntimeException(
                    "A form name is required."
                );
            }

            if (stripos($answer, 'Form') !== false) {
                throw new RuntimeException(
                    "Don't include the word 'Form' in your form name, I'll take care of that."
                );
            }

            if (is_file($modulePath . 'Form' . DIRECTORY_SEPARATOR . $answer . 'Form')) {
                throw new RuntimeException(sprintf("A form with name '%sForm' already exists.", $answer));
            }

            return $answer;
        });

        if (!$formName = $helper->ask($input, $output, $formQuestion)) {
            throw new RuntimeException("A valid form name is required.");
        }

        //
        // 3. Doctrine Entity?
        //
        $useDoctrineQuestion = new Question("<fg=green;options=bold>(3/4) Want to auto add Doctrine bindings? (y/N) </>");
        $useDoctrineQuestion->setValidator(function (?string $answer) {
            $answer = strtolower(trim($answer ?? ''));
            if (!$answer || $answer === 'n' || $answer === 'no') {
                return false;
            }

            if ($answer === 'y' || $answer === 'yes') {
                return true;
            }

            throw new RuntimeException("Hm? Not sure I got that. Please answer yes or no.");
        });

        $useDoctrine = $helper->ask($input, $output, $useDoctrineQuestion);

        //
        // 4. Hydrate a specific class?
        //
        $hydratedClassQuestion = new Question("<fg=green;options=bold>(4/4) Did you want to hydrate a specific class? Enter for none </>");
        $hydratedClass = $helper->ask($input, $output, $hydratedClassQuestion);

        $filesCreated = [
            'Form/' . $formName . 'Form.php',
            'Factory/Form/' . $formName . 'FormFactory.php',
            'InputFilter' . $formName . 'InputFilter.php',
            'Factory/InputFilter/' . $formName . 'InputFilterFactory.php',
        ];

        //
        // Existing file validation, uses PSR4 path style.
        //
        $base = getcwd() . "/module/$module/src/";
        foreach ($filesCreated as $f) {
            $filePath = $base . $f;
            if (file_exists($filePath)) {
                throw new RuntimeException("Sorry! $filePath already exists!");
            }
        }

        $this->writer->setOptions([
            'module' => $module,
            'form' => $formName,
            'hydrateClass' => $hydratedClass,
            'useDoctrine' => $useDoctrine,
        ]);

        $this->openFiles($this->writer->write($output));

        return Command::SUCCESS;
    }

    public static function getWriterService(): string
    {
        return FormWriter::class;
    }
}
