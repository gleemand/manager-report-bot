<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TelegramBot\Api\BotApi;

class TelegramApi
{
    private BotApi $botApi;

    public function __construct(ContainerInterface $container)
    {
        $this->botApi = new BotApi($container->getParameter('tg_token'));
    }

    public function sendMessage(string $chat, string $message)
    {
        $this->botApi->sendMessage($chat, $message, 'Markdown');
    }
}