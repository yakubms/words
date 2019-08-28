<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_allows_alpha_num_username_on_register()
    {
        $request = [
            'name' => 'hoge2',
            'userid' => 'hogehoge1',
            'password' => 'hoge12345',
            'password_confirmation' => 'hoge12345',
            'email' => 'test1@fuga.com'
        ];
        $response = $this->withHeaders([
            'HTTP_REFERER' => '/register',
        ])->post('register', $request);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['userid' => 'hogehoge1']);
    }

    /** @test */
    public function it_disallows_fullwidth_input_on_register()
    {
        $request = [
            'name' => 'hogehoge',
            'userid' => 'ほげ2',
            'password' => 'ほげぴよぽよぽよ',
            'password_confirmation' => 'ほげぴよぽよぽよ',
            'email' => 'test2@fuga.com'
        ];
        $response = $this->withHeaders([
            'HTTP_REFERER' => '/register',
        ])->post('register', $request);

        $response->assertRedirect('register');
        $this->assertDatabaseMissing('users', ['userid' => 'ほげ2']);
    }

    /** @test */
    public function it_disallows_null_input_on_register()
    {
        $request = [
            'name' => '',
            'userid' => '',
            'password' => '',
            'password_confirmation' => '',
            'email' => 'test3@fuga.com'
        ];
        $response = $this->withHeaders([
            'HTTP_REFERER' => '/register',
        ])->post('register', $request);

        $response->assertRedirect('register');
        $this->assertDatabaseMissing('users', ['email' => 'test3@fuga.com']);
    }

    /** @test */
    public function it_allows_null_name_on_register()
    {
        $request = [
            'name' => '',
            'userid' => 'hgoepoyo',
            'password' => 'hogehoge',
            'password_confirmation' => 'hogehoge',
            'email' => 'test4@fuga.com'
        ];
        $response = $this->withHeaders([
            'HTTP_REFERER' => '/register',
        ])->post('register', $request);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['email' => 'test4@fuga.com']);
    }

    /** @test */
    public function it_disallows_symbol_for_userid_on_register()
    {
        $request = [
            'name' => '',
            'userid' => 'hoge#$%&',
            'password' => 'hogehoge',
            'password_confirmation' => 'hogehoge',
            'email' => 'test5@fuga.com'
        ];
        $response = $this->withHeaders([
            'HTTP_REFERER' => '/register',
        ])->post('register', $request);

        $response->assertRedirect('register');
        $this->assertDatabaseMissing('users', ['email' => 'test5@fuga.com']);
    }
}
