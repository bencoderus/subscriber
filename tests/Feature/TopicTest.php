<?php

namespace Tests\Feature;

use App\Jobs\Publisher;
use App\Models\Subscription;
use Tests\TestCase;
use App\Models\Topic;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TopicTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testAClientCanSubscribeToAValidTopic()
    {
        $topic = Topic::factory()->create();

        $payload = ['url' => $this->faker->url];

        $response = $this->post(route('subscribe', ['topic' => $topic]), $payload);

        $response->assertStatus(201)->assertJson(['status' => true]);

        $this->assertDatabaseHas('subscriptions', [
            'topic_id' => $topic->id,
            'url' => $payload['url']
        ]);
    }

    public function testAClientCanNotSubscribeToAnInvalidTopic()
    {
        $payload = ['url' => $this->faker->url];

        $response = $this->post(route('subscribe', ['topic' => 'hello world123']), $payload);

        $response->assertStatus(404)->assertJson(['status' => false]);
    }

    public function testAClientCanNotSubscribeWithAnInvalidUrl()
    {
        $topic = Topic::factory()->create();

        $payload = ['url' => "hello world"];

        $response = $this->post(route('subscribe', ['topic' => $topic]), $payload);

        $response->assertStatus(422)->assertJson(['status' => false, 'errors' => [
            'url' => ['The url format is invalid.']
        ]]);
    }

    public function testAClientCanNotSubscribeWithAnExistingUrl()
    {
        $topic = Topic::factory()->create();

        $subscription = Subscription::factory()->create(['topic_id' => $topic->id]);

        $payload = ['url' => $subscription->url];

        $response = $this->post(route('subscribe', ['topic' => $topic]), $payload);

        $response->assertStatus(422)->assertJson(['status' => false, 'errors' => [
            'url' => ['URL is already subscribed']
        ]]);
    }

    public function testAClientCanPublishToAValidTopic()
    {
        Queue::fake();

        $payload = ['url' => $this->faker->url];

        $topic = Topic::factory()->create();

        $response = $this->post(route('publish', ['topic' => $topic]), $payload);

        Queue::assertNotPushed(Publisher::class);

        $response->assertStatus(200)->assertJson(['status' => true]);

        $this->assertDatabaseHas('published_messages', [
            'topic_id' => $topic->id,
        ]);
    }

    public function testAClientCanNotPublishToAnInvalidTopic()
    {
        $payload = ['url' => $this->faker->url];

        $response = $this->post(route('subscribe', ['topic' => "hello world"]), $payload);

        $response->assertStatus(404)->assertJson(['status' => false]);
    }

    public function testATopicWithSubscribersWouldBeNotifiedUsingAJob()
    {
        Queue::fake();

        $topic = Topic::factory()->create();

        $subscription = Subscription::factory()->create(['topic_id' => $topic->id]);

        $payload = ['message' => "hello world"];

        $response = $this->post(route('publish', ['topic' => $topic]), $payload);

        Queue::assertPushed(Publisher::class);

        $response->assertStatus(200)->assertJson(['status' => true]);

        $this->assertDatabaseHas('published_messages', [
            'topic_id' => $topic->id,
        ]);
    }
}
