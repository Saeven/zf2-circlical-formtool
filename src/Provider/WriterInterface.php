<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Provider;

use Symfony\Component\Console\Output\OutputInterface;

interface WriterInterface
{
    public function write(OutputInterface $output): array;
}
