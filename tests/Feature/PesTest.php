<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
RefreshDatabase::class;
//test('Une fonction de test', function()
//{
//    $nom ="Hamza";
//    expect($nom)->toBe("hamza");
//});
//test('les nombres sont positifs', function ($number) {
//    expect($number)->toBeGreaterThan(0);
//})->with([1, 2, 3, 4]);

//test("Un Utilisateur Peut s'authentifier", function () {
//    $response = $this->post('/api/login', [
//        'email' => "hamzagbouri2004@gmail.com",
//        'password' => "mamababa123"
//    ]);
//    $response->assertStatus(203)
//        ->assertJsonStructure(['token']);
//});
//test("Un Utilisateur Peut s'authentifier avec invalid inputs", function () {
//    $response = $this->post('/api/login', [
//        'email' => "hamzagbouri2004@outloook.com",
//        'password' => "mamababa123"
//    ]);
//    $response->assertStatus(401)
//        ->assertJsonStructure(['error']);
//});

test('un utilisateur ', function(){

    $expectedResponse = ["itineraires" =>  [
    "6" => [
        "itineraire_id" => 6,
            "titre" => "Tour to Youssoufia",
            "duree" => "5 days",
            "image" => "jkreg",
            "user_name" => "hamza",
            "category_name" => "Monument",
            "logement" => "Youssoufia Hotel",
            "nom" => "Youssoufia",
            "activites" => "phosphate",
            "plats" => "twijnat",
            "destinations" => [
                [
                    "logement" => "Youssoufia Hotel",
                    "nom" => "Youssoufia",
                    "activites" => "phosphate",
                    "plats" => "twijnat"
                ],
                [
                    "logement" => "Safi Hotel",
                    "nom" => "Safi",
                    "activites" => "phosphate",
                    "plats" => "tanjia"
                ]
            ]
        ]
]];
    $response = $this->get('/api/itineraires');
    $response->assertJson($expectedResponse);


});
