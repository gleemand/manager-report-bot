<?php

namespace App\Service;

use App\Report\Report;
use RetailCrm\Api\Model\Entity\Users\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReportService
{
    private CrmApi $crmApi;
    private TelegramApi $telegramApi;
    private ContainerInterface $container;

    public function __construct(CrmApi $crmApi, TelegramApi $telegramApi, ContainerInterface $container)
    {
        $this->crmApi = $crmApi;
        $this->telegramApi = $telegramApi;
        $this->container = $container;
    }

    public function execute(): void
    {
        $managers = $this->getManagers();

        foreach ($managers as $manager) {
            $report = $this->buildReport($manager);

            $this->sendReport($report);
        }
    }

    /**
     * @return User[]
     */
    private function getManagers(): array
    {
        return $this->crmApi->getUsersByGroup($this->container->getParameter('crm_group'));
    }

    private function buildReport(User $manager): Report
    {
        $reportDate = new \DateTimeImmutable('yesterday');

        $dialogs = $this->crmApi->getDialogsByManagerAndDate($manager->mgUserId, $reportDate);
        $orders = $this->crmApi->getOrdersByManagerAndDate($manager->id, $reportDate, array_keys(Report::STATUSES));

        return new Report(
            $reportDate,
            count($dialogs),
            $orders,
            $this->buildTagForManager($manager),
            $this->container->getParameter('crm_api_url')
        );
    }

    private function sendReport(Report $report): void
    {
        $this->telegramApi->sendMessage($this->container->getParameter('tg_chat'), $report->generate());
    }

    private function buildTagForManager(User $manager): string
    {
        return '#' . mb_strtolower(current(explode(' ', $manager->firstName)));
    }
}