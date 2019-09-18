<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_it_can_create_tasks_from_text()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer ' . $user->api_token])
            ->json('POST', '/api/tasks/create', [
                'file' => new UploadedFile(__DIR__ . '/testDictionary.txt', 'testDictionary.txt', null, null, null, true)]);
        $response->assertStatus(200);
        $response->assertJson(['count' => 4]);
    }
}
