<?php

namespace App\Report;

class Report
{
    public const STATUSES = [
        '2' => '📤 Сделали первый контакт',
        'prezentatsiia-naznachena' => '🗓 Презентация назначена',
        'provedena-prezentatsiia' => '🖥  Проведено презентаций',
        'prezentatsiia-perenesena' => '🕐 Презентация перенесена',
        'kp-otpravleno' => '📩 КП отправлено',
        'waiting-for-1st-payment' => '💸 Счетов выставлено',
        'poluchen-1-platezh-1' => '💰Получено платежей',
        'popytka-kasaniia-1' => '🟠 Попытка касания 1',
        'popytka-kasaniia-2' => '🟡 Попытка касания 2',
        'popytka-kasaniia-3' => '🔵 Попытка касания 3',
    ];

    private \DateTimeImmutable $date;
    private int $dialogsCount;
    private array $orders;
    private string $tag;
    private string $crmUrl;

    public function __construct(
        \DateTimeImmutable $date,
        int $dialogsCount,
        array $orders,
        string $tag,
        string $crmUrl
    ) {
        $this->date = $date;
        $this->dialogsCount = $dialogsCount;
        $this->orders = $orders;
        $this->tag = $tag;
        $this->crmUrl = $crmUrl;
    }

    public function generate(): string
    {
        $output = [];
        $output[] = sprintf('*%s*', $this->date->format('d.m.Y'));
        $output[] = '';
        $output[] = sprintf('*💬 Диалогов: %d*', $this->dialogsCount);
        $output[] = '';
        $output[] = $this->buildOrders();
        $output[] = sprintf('_%s_', $this->tag);

        return implode("\n", $output);
    }

    private function buildOrders(): string
    {
        $output = [];

        foreach (self::STATUSES as $code => $name) {
            $orders = $this->getOrdersByStatus($code);
            $output[] = sprintf('*%s: %d*', $name, count($orders));

            $index = 1;
            foreach ($orders as $order) {
                $output[] = sprintf('%d. [%s](%sorders/%d/edit)', $index++, $order->number, $this->crmUrl, $order->id);
            }

            $output[] = '';
        }

        return implode("\n", $output);
    }

    private function getOrdersByStatus(string $status): array
    {
        return array_filter($this->orders, static fn($order) => $order->status === $status);
    }
}