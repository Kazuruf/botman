<?php

namespace Mpociot\BotMan\Tests\Middleware;

use Mockery as m;
use Mpociot\BotMan\Message;
use Mpociot\BotMan\Http\Curl;
use PHPUnit_Framework_TestCase;
use Mpociot\BotMan\Middleware\ApiAi;
use Mpociot\BotMan\Drivers\NullDriver;
use Symfony\Component\HttpFoundation\Response;

class ApiAiTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_adds_entities_to_the_message()
    {
        $messageText = 'This will be my message text!';
        $message = new Message($messageText, '', '');

        $apiResponse = [
            'result' => [
                'speech' => 'api reply text',
                'action' => 'api action name',
                'metadata' => [
                    'intentName' => 'name of the matched intent',
                ],
            ],
        ];
        $response = new Response(json_encode($apiResponse));

        $http = m::mock(Curl::class);
        $http->shouldReceive('post')
            ->once()
            ->with('https://api.api.ai/v1/query', [], [
                'query' => [$messageText],
                'sessionId' => time(),
                'lang' => 'en',
            ], [
                'Authorization: Bearer token',
                'Content-Type: application/json; charset=utf-8',
            ], true)
            ->andReturn($response);

        $middleware = new ApiAi('token', $http);
        $middleware->handle($message, m::mock(NullDriver::class));

        $this->assertSame([
            'apiReply' => 'api reply text',
            'apiAction' => 'api action name',
            'apiIntent' => 'name of the matched intent',
        ], $message->getExtras());
    }

    /** @test */
    public function it_can_be_created()
    {
        $middleware = ApiAi::create('token');
        $this->assertInstanceOf(ApiAi::class, $middleware);
    }
}
