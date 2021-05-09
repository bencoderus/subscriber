<?php

namespace App\Jobs;

use App\Models\PublishedMessage;
use App\Models\Topic;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class Publisher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry interval in minutes.
     */
    public const RETRY_INTERVAL_IN_MINUTES = 30;

    /**
     * The topic instance
     *
     * @var \App\Models\Topic
     */
    public $topic;

    /**
     * The message we want to publish.
     *
     * @var \App\Models\PublishedMessage
     */
    public $message;

    /**
     * The Number of times a webhook would be retried.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Topic $topic
     * @param \App\Models\PublishedMessage $message
     */
    public function __construct(Topic $topic, PublishedMessage $message)
    {
        $this->topic = $topic;
        $this->message = $message;
    }

    /**
     * Handle the publisher job.
     */
    public function handle()
    {
        $topic = $this->createPublishedLogsForSubscribers($this->topic, $this->message);

        $sendingCompleted = $this->sendWebhookToSubscribers($topic);

        if (! $sendingCompleted) {
            $this->retryWebhook();
        }
    }

    /**
     * Create a new published logs for the subscribers.
     *
     * @param \App\Models\Topic $topic
     * @param \App\Models\PublishedMessage $message
     *
     * @return \App\Models\Topic
     */
    private function createPublishedLogsForSubscribers(Topic $topic, PublishedMessage $message): Topic
    {
        if ($this->attempts() > 1) {
            return $topic->load(['subscriptions', 'publishedLogs']);
        }

        $subscriptions = collect($topic->subscriptions)->map(function ($subscription) use ($message) {
            return [
                'subscription_id' => $subscription->id,
                'published_message_id' => $message->id,
            ];
        });

        $topic->publishedLogs()->createMany($subscriptions->toArray());

        return $topic->load(['subscriptions', 'publishedLogs']);
    }

    /**
     * Send out webhooks to the subscribers.
     *
     * @param \App\Models\Topic $topic
     *
     * @return bool
     */
    private function sendWebhookToSubscribers(Topic $topic): bool
    {
        $webhooks = $topic->publishedLogs()
            ->with('subscription')
            ->where('has_received', false)
            ->get()->lazy();

        $sendingCompleted = true;

        foreach ($webhooks as $webhook) {
            try {
                $statusCode = Http::timeout(15)
                    ->post($webhook->subscription->url, $this->message->payload)
                    ->throw()
                    ->status();

                if ($statusCode >= 200 && $statusCode <= 205) {
                    $webhook->update(['has_received' => true, 'received_at' => now()]);
                } else {
                    $sendingCompleted = false;
                }
            } catch (Exception $exception) {
                report($exception);

                $sendingCompleted = false;

                continue;
            }
        }

        return $sendingCompleted;
    }

    /**
     * Retry the webhook job.
     */
    private function retryWebhook()
    {
        ($this->attempts() <= $this->tries)
            ? $this->release(self::RETRY_INTERVAL_IN_MINUTES * 60)
            : $this->fail();
    }
}
