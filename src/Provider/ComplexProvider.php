<?php

namespace App\Provider;

use App\Provider;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ComplexProvider extends Provider
{
    public function getShortCodeByUrl(string $url): ?string
    {
        $shortCode = $this->generateShortCode($url);

        $this->publishSaveQueueMessage($url, $shortCode);

        return $shortCode;
    }

    public function matchShortCode(string $shortCode): bool
    {
        return strlen($shortCode) == 32 && ctype_xdigit($shortCode);
    }

    public function getUrlByShortCode(string $shortCode): ?string
    {
        return $this->app->getRedis()->get($shortCode) ?: null;
    }

    /**
     * Simple short code generation
     *
     * @param string $url
     *
     * @return string
     */
    private function generateShortCode(string $url)
    {
        return md5($url);
    }

    private function prepareAmpq(): array
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, getenv('RABBITMQ_USER'), getenv('RABBITMQ_PASS'));

        $channel = $connection->channel();

        $queue = 'save_url_and_short_code';
        $channel->queue_declare($queue, false, true, false, false);

        return [$connection, $queue, $channel];
    }

    private function publishSaveQueueMessage(string $url, string $shortCode)
    {
        /** @var AMQPStreamConnection $connection */
        /** @var AMQPChannel $channel */

        list($connection, $queue, $channel) = $this->prepareAmpq();

        $msg = new AMQPMessage($url . ' ' . $shortCode, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $channel->basic_publish($msg, '', $queue);

        $channel->close();
        $connection->close();
    }

    public function listenSaveQueueMessage()
    {
        /** @var AMQPChannel $channel */

        list(, $queue, $channel) = $this->prepareAmpq();

        $channel->basic_qos(null, 1, null);

        $channel->basic_consume($queue, '', false, false, false, false, function (AMQPMessage $msg) {
            list($url, $shortCode) = explode(' ', $msg->getBody());

            //other stuff goes here: counters, last visit etc.

            if (!$this->app->getRedis()->get($shortCode)) {
                $this->app->getRedis()->set($shortCode, $url);
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        });

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}