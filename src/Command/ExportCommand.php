<?php


namespace App\Command;

use App\Service\EmailService;
use App\Service\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    protected static $defaultName = 'app:export';

    private $exportService;
    private $emailService;

    public function __construct(ExportService $exportService, EmailService $emailService)
    {
        $this->exportService = $exportService;
        $this->emailService = $emailService;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Export default input to any supported format.');
        $this->addArgument('filename', InputArgument::REQUIRED, 'The output filename.');
        $this->addArgument('format', InputArgument::REQUIRED, 'Supported formats (csv, json, yaml, xml)');
        $this->addArgument('email', InputArgument::OPTIONAL, 'Send file exported to your email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $supportedFormats = ['csv', 'json', 'yaml', 'xml'];
        $filename = $input->getArgument('filename');
        $format = $input->getArgument('format');
        $email = $input->getArgument('email');
        $file = sprintf("exportedFiles/%s.%s", $filename, $format);

        if (!in_array($format, $supportedFormats)) {
            $output->writeln('<error>Please check your format input!</error>');
            $output->writeln('<info>Supported formats is (csv, json, yaml, xml)</info>');
            return;
        }

        $output->writeln('Exporting, please wait...');
        $output->writeln($this->exportService->run($filename, $format));

        if ($email) {
            $output->writeln('Validate your email...');

            if (!$this->emailService->validate($email)) {
                $output->writeln('<error>Email is not valid!</error>');
                $output->writeln('Failed sending this file!');
                return;
            }

            $output->writeln('Sending file to your email, please wait...');
            $output->writeln($this->emailService->send($email, $file));
            $output->writeln('<info>Email has been sent!</info>');
        }

        $executionTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        $output->write("Execution Time: " . $executionTime);
    }
}
