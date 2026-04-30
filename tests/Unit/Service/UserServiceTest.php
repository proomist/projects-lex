<?php

use App\Service\UserService;
use App\Repository\UserRepository;
use Monolog\Logger;

test('it throws exception when user login fails', function () {
    // Arrange (Hazırlık)
    $mockRepo = Mockery::mock(UserRepository::class);
    $mockRepo->shouldReceive('findByUsernameOrEmail')
        ->once()
        ->with('invalid_user', 'invalid_user')
        ->andReturn(null);

    // Logger mock için expectation ekleyelim (logActivity userRepository üzerinden yapılıyor)
    $mockRepo->shouldReceive('logActivity')
        ->once()
        ->with(0, 'login_failed_user_not_found', 'Auth', '127.0.0.1');

    $service = new UserService($mockRepo);

    // Act & Assert (Eylem ve Doğrulama)
    expect(fn() => $service->login('invalid_user', 'password123', '127.0.0.1'))
        ->toThrow(Exception::class, 'Kullanıcı adı veya şifre hatalı.');
});
