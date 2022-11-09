<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Command;

use Circlical\LaminasTools\Provider\WriterInterface;
use Symfony\Component\Console\Command\Command;

use function getcwd;
use function shell_exec;
use function sprintf;

use const DIRECTORY_SEPARATOR;

abstract class AbstractCommand extends Command
{
    private ?string $editCommand;

    protected WriterInterface $writer;

    abstract public static function getWriterService(): string;

    protected function getModulePath(): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR;
    }

    public function __construct(array $options, WriterInterface $writer)
    {
        $this->editCommand = $options['command']['edit'] ?? null;
        $this->writer = $writer;

        parent::__construct();
    }

    protected function openFiles(array $createdFiles): void
    {
        foreach ($createdFiles as $createdFile) {
            if (!$createdFile) {
                continue;
            }
            shell_exec(sprintf($this->editCommand, $createdFile));
        }
    }
}
