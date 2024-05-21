<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use RetailCrm\Api\Client;
use RetailCrm\Mg\Bot\Client as MgClient;
use RetailCrm\Api\Enum\NumericBoolean;
use RetailCrm\Api\Enum\PaginationLimit;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Model\Filter\Orders\OrderFilter;
use RetailCrm\Api\Model\Filter\Users\ApiUserFilter;
use RetailCrm\Api\Model\Request\Orders\OrdersRequest;
use RetailCrm\Api\Model\Request\Users\UsersRequest;
use RetailCrm\Mg\Bot\Model\Request\DialogsRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CrmApi
{
    private Client $client;
    private MgClient $mgClient;
    private LoggerInterface $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->client = SimpleClientFactory::createClient(
            $container->getParameter('crm_api_url'),
            $container->getParameter('crm_api_key')
        );

        $this->mgClient = new MgClient(
            $container->getParameter('mg_api_url'),
            $container->getParameter('mg_api_key')
        );
    }

    public function getUsersByGroup(string $group): array
    {
        $request                 = new UsersRequest();
        $request->filter         = new ApiUserFilter();
        $request->filter->active = NumericBoolean::TRUE;
        $request->filter->groups = [$group];

        try {
            $response = $this->client->users->list($request);
        } catch (ApiExceptionInterface $exception) {
            $this->logger->error(sprintf(
                'Error from RetailCRM API (status code: %d): %s',
                $exception->getStatusCode(),
                $exception->getMessage()
            ));

            if (count($exception->getErrorResponse()->errors) > 0) {
                $this->logger->error('Errors: ' . implode(', ', $exception->getErrorResponse()->errors));
            }

            return [];
        }

        return $response->users;
    }

    public function getOrdersByManagerAndDate(int $managerId, \DateTimeImmutable $day, array $statuses): array
    {
        $request = new OrdersRequest();
        $request->limit = PaginationLimit::LIMIT_100;
        $request->page = 1;
        $request->filter = new OrderFilter();
        $request->filter->managers = [$managerId];
        //$request->filter->createdAtFrom = $request->filter->createdAtTo = $day->format('Y-m-d');
        $request->filter->statusUpdatedAtFrom = $request->filter->statusUpdatedAtTo = $day->format('Y-m-d');

        $orders = [];

        do {
            try {
                $response = $this->client->orders->list($request);
            } catch (ApiExceptionInterface $exception) {
                $this->logger->error(sprintf(
                    'Error from RetailCRM API (status code: %d): %s',
                    $exception->getStatusCode(),
                    $exception->getMessage()
                ));

                if (count($exception->getErrorResponse()->errors) > 0) {
                    $this->logger->error('Errors: ' . implode(', ', $exception->getErrorResponse()->errors));
                }

                return [];
            }

            if (empty($response->orders)) {
                break;
            }

            $orders = array_merge($orders, $response->orders);

            ++$request->page;
        } while ($response->pagination->currentPage < $response->pagination->totalPageCount);

        return $orders;
    }

    public function getDialogsByManagerAndDate(int $managerMgId, \DateTimeImmutable $day): array
    {
        $request = new DialogsRequest();
        $request->setUserId($managerMgId);
        $request->setSince(new \DateTime('00:00:00 ' . $day->format('Y-m-d')));
        $request->setUntil(new \DateTime('23:59:59 ' . $day->format('Y-m-d')));

        try {
            $response = $this->mgClient->dialogs($request);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return [];
        }

        return $response;
    }
}