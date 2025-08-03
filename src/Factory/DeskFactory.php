<?php

namespace App\Factory;

use App\Entity\Desk;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Desk>
 */
final class DeskFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Desk::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'capacity' => self::faker()->randomNumber(),
            'description' => self::faker()->text(),
            'isAvailable' => self::faker()->boolean(),
            'name' => self::faker()->text(60),
            'pricePerDay' => self::faker()->randomFloat(),
            'space' => SpaceFactory::new(),
            'type' => self::faker()->randomNumber(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Desk $desk): void {})
        ;
    }
}
