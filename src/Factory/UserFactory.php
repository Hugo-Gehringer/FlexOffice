<?php

namespace App\Factory;

use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'firstname' => self::faker()->firstName(),
            'lastname' => self::faker()->lastName(),
            'password' => 'password123',
            'roles' => ['ROLE_USER'],
            'isVerified' => true,
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function(User $user): void {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $user->getPassword())
            );
        });
    }
}