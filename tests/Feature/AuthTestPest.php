<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
     * A basic feature test example.
     *
     * @return void
     */
//test('Un utilisateur peut se connecter', function () {
//    $mockUser = Mockery::mock(  'overload:' . User::class);
//    $mockUser->shouldReceive('where->first')->once()->andReturn((object) [
//        'id' => 1,
//        'name' => 'Test User',
//        'email' => 'test@example.com',
//        'password' => Hash::make('password123'),
//    ]);
//
//    JWTAuth::shouldReceive('attempt')->once()->andReturn('fake-jwt-token');
//
//    $response = $this->postJson('/api/login', [
//        'email' => 'test@example.com',
//        'password' => 'password123',
//    ]);
//
//    $response->assertStatus(200)
//        ->assertJsonStructure(['token']);
//});

