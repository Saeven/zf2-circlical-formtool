<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Command;

use Symfony\Component\Console\Command\Command;

class AbstractCommand extends Command
{
    private ?string $editCommand;

    protected function getModulePath(): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR;
    }

    public function __construct(array $options)
    {
        $this->editCommand = $options['command']['edit'] ?? null;
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

