<?php

namespace App\Command;

use App\Service\ImportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportCommand extends Command
{
    protected static $defaultName = 'app:import';

    private $importService;

    public function __construct(ImportService $importService)
    {
        parent::__construct();

        $this->importService = $importService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import default file to database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Importing, please wait...');

        $io->success($this->importService->run());

        $executionTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        $io->note("Execution Time: " . $executionTime);
    }
}
