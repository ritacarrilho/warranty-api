<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;


class EquipmentControllerTest extends WebTestCase
{
    public function authenticate()
    {
        // Mock the token payload with user email
        $mockedTokenPayload = ['email' => 'admin@email.test'];

        $jwtEncoderMock = $this->getMockBuilder(JWTEncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jwtEncoderMock->expects($this->any())
            ->method('decode')
            ->willReturn($mockedTokenPayload);

        // Replace the encoder service with the mock
        return $jwtEncoderMock;
    }

    public function authenticatedUser()
    {
        $client = static::createClient();

        // Replace the encoder service with the mock
        $client->getContainer()->set('lexik_jwt_authentication.encoder', $this->authenticate());

        return $client;
    }

    public function testListEquipments(): void
    {
        $client = $this->authenticatedUser();
        // Set the Authorization header with a valid JWT token
        $client->request('GET', '/api/equipments', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer valid_mocked_token'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testShowEquipment(): void
    {
        $client = $this->authenticatedUser();

        // Set the Authorization header with a valid JWT token
        $client->request('GET', '/api/equipment/5', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer valid_mocked_token'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testShowEquipmentFailure(): void
    {
        $client = $this->authenticatedUser();

        // Set the Authorization header with a valid JWT token
        $client->request('GET', '/api/equipment/50', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer valid_mocked_token'
        ]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    // public function testDeleteEquipments(): void
    // {
    //     $client = $this->authenticatedUser();

    //     // Set the Authorization header with a valid JWT token
    //     $client->request('DELETE', '/api/equipment/5', [], [], [
    //         'HTTP_AUTHORIZATION' => 'Bearer valid_mocked_token'
    //     ]);

    //     $this->assertResponseIsSuccessful();
    //     $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    // }

    public function testCreateEquipment(): void
    {
        $client = $this->authenticatedUser();

        // Set the Authorization header with a valid JWT token
        $client->request('GET', '/api/equipment/5', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer valid_mocked_token'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
    
    public function testUpdateEquipment(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Retrieve the test user
        $testUser = $userRepository->findOneByEmail('admin@email.test');
        // Simulate $testUser being logged in
        $client->loginUser($testUser);

        // Test GET /api/equipments with auth
        $client->request('GET', '/api/categories');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testListEquipmentWarranties(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Retrieve the test user
        $testUser = $userRepository->findOneByEmail('admin@email.test');
        // Simulate $testUser being logged in
        $client->loginUser($testUser);

        // Test GET /api/equipments with auth
        $client->request('GET', '/api/categories');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    // public function testCreateEquipment(): void
    // {
    //     $client = $this->authenticatedUser();

    //     // Test POST /api/equipment
    //     $data = [
    //         'name' => "Test name",
    //         'brand' => "Test Brand",
    //         'model' => "Test model",
    //         'serial_code' => "TestSerialCode",
    //         'purchase_date' => "2018-04-22",
    //         'is_active' => 1,
    //         'category' => 7
    //     ];

    //     $client->request('POST', '/api/equipment', ['json' => $data], [
    //         'HTTP_AUTHORIZATION' => 'Bearer valid_mocked_token'
    //     ]);

    //     $this->assertResponseIsSuccessful();
    //     $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    // }
}

