<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TelegramBot\Api\BotApi;

class TelegramApi
{
    private BotApi $botApi;
    private LoggerInterface $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->botApi = new BotApi($container->getParameter('tg_token'));
        $this->logger = $logger;
    }

    public function sendMessage(string $chat, string $message)
    {
        try {
            $this->botApi->sendMessage($chat, $message, 'Markdown');
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}