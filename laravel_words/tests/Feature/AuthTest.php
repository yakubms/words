<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_logged_in_user_is_redirected_to_home()
    {
        $user = factory(\App\User::class)->create();

        $response = $this->actingAs($user)->get('/');
        $response->assertViewIs('app');
    }
}
