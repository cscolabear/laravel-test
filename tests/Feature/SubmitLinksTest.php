<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubmitLinksTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_can_submit_a_new_link()
    {
        $response = $this->post('/submit', [
            'title' => 'Example Title',
            'url' => 'http://example.com',
            'description' => 'Example description.',
        ]);

        $this->assertDatabaseHas('links', [
            'title' => 'Example Title'
        ]);

        $response
            ->assertStatus(302)
            ->assertHeader('Location', url('/'));

        $this
            ->get('/')
            ->assertSee('Example Title');
    }

    /** @test */
    public function link_is_not_created_if_validation_fails()
    {
        $response = $this->post('/submit');

        $response->assertSessionHasErrors(['title', 'url', 'description']);
    }

    /**
     * @test
     */
    public function link_is_not_created_with_an_invalid_url()
    {
        $this->withoutExceptionHandling();

        $cases = ['//invalid-url.com', '/invalid-url', 'foo.com'];

        foreach ($cases as $case) {
            try {
                $response = $this->post('/submit', [
                    'title' => 'Example Title',
                    'url' => $case,
                    'description' => 'Example description',
                ]);
            } catch (ValidationException $e) {
                $this->assertEquals(
                    'The url format is invalid.',
                    $e->validator->errors()->first('url')
                );
                continue;
            }

            $this->fail("The URL $case passed validation when it should have failed.");
        }
    }

    /** @test */
    public function max_length_fails_when_too_long()
    {
        $this->withoutExceptionHandling();

        $title = str_repeat('a', 256);
        $description = str_repeat('a', 256);
        $url = 'http://';
        $url .= str_repeat('a', 256 - strlen($url));

        try {
            $this->post('/submit', compact('title', 'url', 'description'));
        } catch(ValidationException $e) {
            $this->assertEquals(
                'The title may not be greater than 255 characters.',
                $e->validator->errors()->first('title')
            );

            $this->assertEquals(
                'The url may not be greater than 255 characters.',
                $e->validator->errors()->first('url')
            );

            $this->assertEquals(
                'The description may not be greater than 255 characters.',
                $e->validator->errors()->first('description')
            );

            return;
        }

        $this->fail('Max length should trigger a ValidationException');
    }

    /** @test */
    public function max_length_succeeds_when_under_max()
    {
        $url = 'http://';
        $url .= str_repeat('a', 255 - strlen($url));

        $data = [
            'title' => str_repeat('a', 255),
            'url' => $url,
            'description' => str_repeat('a', 255),
        ];

        $this->post('/submit', $data);

        $this->assertDatabaseHas('links', $data);
    }

    /** @test */
    public function link_is_not_created_with_an_url_cant_be_reached()
    {
        $this->withoutExceptionHandling();

        $mock_rule = Mockery::mock(\App\Rules\UrlExists::class);
        $mock_rule->shouldReceive('message')->once()->andReturn('This url can’t be reached');
        $mock_rule->shouldReceive('passes')->once()->andReturn(false);
        $this->app->instance(\App\Rules\UrlExists::class, $mock_rule);

        try {
            $response = $this->post('/submit', [
                'title' => 'url_cant_be_reached',
                'url' => 'https://iamcola.io',
                'description' => 'Example description.',
                'check_url' => 1, // checkbox, key exists is checked
            ]);
        } catch(ValidationException $e) {
            $this->assertEquals(
                'This url can’t be reached',
                $e->validator->errors()->first('url')
            );
            return;
        }

        $this->fail('The URL passed validation when it should have failed.');
    }
}
