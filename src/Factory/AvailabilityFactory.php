<?php

namespace App\Factory;

use App\Entity\Availability;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Availability>
 */
final class AvailabilityFactory extends PersistentProxyObjectFactory
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
        return Availability::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'friday' => self::faker()->boolean(),
            'monday' => self::faker()->boolean(),
            'saturday' => self::faker()->boolean(),
            'space' => SpaceFactory::new(),
            'sunday' => self::faker()->boolean(),
            'thursday' => self::faker()->boolean(),
            'tuesday' => self::faker()->boolean(),
            'wednesday' => self::faker()->boolean(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Availability $availability): void {})
        ;
    }
}
