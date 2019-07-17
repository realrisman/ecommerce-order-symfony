<?php


namespace App\Command;

use App\Service\EmailService;
use App\Service\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $io = new SymfonyStyle($input, $output);
        $supportedFormats = ['csv', 'json', 'yaml', 'xml'];
        $filename = $input->getArgument('filename');
        $format = $input->getArgument('format');
        $email = $input->getArgument('email');
        $file = sprintf("exportedFiles/%s.%s", $filename, $format);

        if (!in_array($format, $supportedFormats)) {
            $io->error('Please check your format input!');
            $io->note('Supported formats is (csv, json, yaml, xml)');
            return;
        }

        $io->section('Exporting, please wait');
        $io->success($this->exportService->run($filename, $format));

        if ($email) {
            $io->section('Validate your email');

            if (!$this->emailService->validate($email)) {
                $io->error('Email is not valid!');
                $io->note('Failed sending this file!');
                return;
            }

            $io->block("Email is valid!");

            $io->section('Sending file to your email, please wait');
            $this->emailService->send($email, $file);
            $io->success('Email has been sent!');
        }

        $executionTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        $io->note("Execution Time: " . $executionTime);
    }
}
