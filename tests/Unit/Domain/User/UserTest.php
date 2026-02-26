<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use App\Domain\Shared\ValueObjects\Email;
use App\Domain\User\User;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_user_name_must_be_at_least_2_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User name must be at least 2 characters.');

        new UserName('A');
    }

    public function test_user_name_must_not_exceed_255_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User name must not exceed 255 characters.');

        new UserName(str_repeat('a', 256));
    }

    public function test_email_must_be_valid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('invalid-email');
    }

    public function test_email_is_normalized_to_lowercase(): void
    {
        $email = new Email('JAN@EXAMPLE.COM');

        $this->assertEquals('jan@example.com', $email->value);
    }

    public function test_to_array_returns_correct_data(): void
    {
        $user = User::create(
            name: 'Jan de Vries',
            email: 'jan@example.com',
            password: 'password123',
        );

        $array = $user->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('password', $array);
        $this->assertEquals('Jan de Vries', $array['name']);
        $this->assertEquals('jan@example.com', $array['email']);
    }
}

