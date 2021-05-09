<?php

namespace App\Http\Controllers;

use App\Jobs\Publisher;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Subscribe to a topic.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Topic $topic
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe(Request $request, Topic $topic): JsonResponse
    {
        $request->validate(['url' => 'required|string|url|unique:subscriptions,url'], ['url.unique' => 'URL is already subscribed']);

        $subscription = $topic->subscriptions()->create(['url' => $request->url]);

        return $this->createdResponse("Subscription added successfully", [
            'topic' => $topic->title,
            'url' => $subscription->url
        ]);
    }

    /**
     * Publish a message to the topic subscribers.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Topic $topic
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(Request $request, Topic $topic): JsonResponse
    {
        $payload = [
            'topic' => $topic->title,
            'data' => $request->input()
        ];

        $message = $topic->publishedMessages()->create(['payload' => $payload]);

        $topicWithSubscriptions = $topic->load('subscriptions');

        if ($topicWithSubscriptions->subscriptions()->exists()) {
            Publisher::dispatch($topicWithSubscriptions, $message);
        }

        return $this->okResponse("Message has been published");
    }
}
