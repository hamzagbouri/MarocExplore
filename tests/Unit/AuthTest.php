<?php

namespace Tests\Unit;



use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
//    public function test_login_with_invalid_credentials()
//    {
//        $response = $this->postJson('/api/login', [
//            'email' => 'user@example.com',
//            'password' => 'password123'
//        ]);
//
//        $response->assertStatus(401)
//            ->assertJsonStructure([
//                'error'
//            ]);
//    }
//    public function test_login_with_valid_credentials()
//    {
//        $response = $this->postJson('/api/login', [
//            'email' => 'hamzagbouri2004@gmail.com',
//            'password' => 'mamababa123'
//        ]);
//
//        $response->assertStatus(200)
//            ->assertJsonStructure([
//                'token'
//            ]);
//    }

//    public function test_login_with_invalid_email()
//    {
//        $response = $this->postJson('/api/login', [
//            'email' => 'invalid-email',
//            'password' => 'password123'
//        ]);
//
//        $response->assertStatus(401)
//        ->assertJsonStructure(['error']);
//    }
    public function test_register_with_valid_credentials()
    {

       $response = $this->postJson('/api/register', [
            'email' => 'hamza.gbouri@gmail.com',
            'password' => 'mamababa123',
            'name' => 'Hamza'
        ]);
        $response->assertStatus(201)
            ->assertJsonStructure(['token']);
    }
//    public function test_register_with_invalid_credentials()
//    {
//        $response = $this->postJson('/api/register', [
//            'email' => 'hamzagbouri2004@gmail.com',
//            'password' => 'mamababa123',
//            'name' => 'Hamza'
//        ]);
//        $response->assertStatus(400);
//
//
//    }
}
