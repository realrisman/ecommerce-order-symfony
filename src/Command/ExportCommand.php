<?php


namespace App\Command;


use App\Service\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    protected static $defaultName = 'app:export';

    private $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Export default input to any supported format.');
        $this->addArgument('filename', InputArgument::REQUIRED, 'The output filename.');
        $this->addArgument('format', InputArgument::REQUIRED, 'Supported formats (csv, json, yaml, xml)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $supportedFormats = ['csv', 'json', 'yaml', 'xml'];
        $filename = $input->getArgument('filename');
        $format = $input->getArgument('format');
        $file = $filename.'.'.$format;

        if (!in_array($format, $supportedFormats)) {
            $output->writeln('<error>Please check your format input!</error>');
            $output->writeln('<info>Supported formats is (csv, json, yaml, xml)</info>');
            return;
        }

        $output->writeln('Exporting, please wait...');

        $output->writeln($this->exportService->run($filename, $format));

        $executionTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        $output->write("Execution Time: ".$executionTime);
    }
}