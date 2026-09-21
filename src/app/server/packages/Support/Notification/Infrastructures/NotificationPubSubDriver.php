<?php

declare(strict_types=1);

namespace Support\Notification\Infrastructures;

use Google\Cloud\PubSub\V1\Client\PublisherClient;
use Google\Cloud\PubSub\V1\PublishRequest;
use Google\Cloud\PubSub\V1\PubsubMessage;
use Override;
use Support\Notification\Contracts\NotificationDriverInterface;
use Support\Notification\Contracts\NotificationMessage;

final readonly class NotificationPubSubDriver implements NotificationDriverInterface
{
    public function __construct(
        private NotificationConfig $config,
        private PublisherClient $publisher,
    ) {
    }

    #[Override]
    public function notice(NotificationMessage $message): void
    {
        $this->publisher->publish(
            new PublishRequest()
                ->setTopic(PublisherClient::topicName($this->config->project, $this->config->topic))
                ->setMessages([new PubsubMessage()->setData($message->toJson())]),
        );
    }
}
