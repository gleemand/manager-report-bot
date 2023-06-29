<?php

namespace App\Command;

use App\Service\ReportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendReportCommand extends Command
{
    protected static $defaultName = 'app:send-report';
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->reportService->execute();

        return Command::SUCCESS;
    }
}
