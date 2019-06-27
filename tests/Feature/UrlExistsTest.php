<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;

class UrlExistsTest extends TestCase
{
    /** @test */
    public function check_url_pass()
    {
        $url = 'https://www.google.com.tw';
        $mock_helper = Mockery::mock(\App\Rules\UrlExistsHelper::class);
        $mock_helper->shouldReceive('isExists')->with($url)->once()->andReturn(true);
        $this->app->instance(\App\Rules\UrlExistsHelper::class, $mock_helper);

        $rule = \App::make(\App\Rules\UrlExists::class);
        $this->assertTrue($rule->passes('url', $url));
    }

    /** @test */
    public function check_url_fail()
    {
        $mock_helper = Mockery::mock(\App\Rules\UrlExistsHelper::class);
        $mock_helper->shouldReceive('isExists')->once()->andReturn(false);
        $this->app->instance(\App\Rules\UrlExistsHelper::class, $mock_helper);

        $rule = \App::make(\App\Rules\UrlExists::class);
        $this->assertFalse($rule->passes('attribute', 'https://iamcola.io'));
    }
}
