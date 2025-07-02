<?php

namespace App\Tests\Entity;

use App\Entity\Address;
use App\Entity\Space;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddressTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get('doctrine')->getManager();
        $this->validator = $container->get(ValidatorInterface::class);

        // Schema reset (in-memory SQLite)
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
    }

    private function makeValidAddress(): Address
    {
        $address = new Address();
        $address->setStreet('123 Rue de la Paix');
        $address->setCity('Montpellier');
        $address->setPostalCode('34000');
        $address->setCountry('France');
        return $address;
    }

    public function testValidAddressIsPersisted(): void
    {
        $address = $this->makeValidAddress();

        $this->em->persist($address);
        $this->em->flush();

        $repo = $this->em->getRepository(Address::class);
        $found = $repo->findOneBy(['street' => '123 Rue de la Paix']);

        $this->assertNotNull($found);
        $this->assertEquals('123 Rue de la Paix', $found->getStreet());
        $this->assertEquals('Montpellier', $found->getCity());
        $this->assertEquals('34000', $found->getPostalCode());
        $this->assertEquals('France', $found->getCountry());
    }

    public function testStreetIsRequired(): void
    {
        $address = $this->makeValidAddress();
        $address->setStreet('');

        $errors = $this->validator->validate($address);
        $this->assertGreaterThan(0, count($errors), 'Blank street should trigger validation error.');
    }

    public function testCityIsRequired(): void
    {
        $address = $this->makeValidAddress();
        $address->setCity('');

        $errors = $this->validator->validate($address);
        $this->assertGreaterThan(0, count($errors), 'Blank city should trigger validation error.');
    }

    public function testPostalCodeIsRequired(): void
    {
        $address = $this->makeValidAddress();
        $address->setPostalCode('');

        $errors = $this->validator->validate($address);
        $this->assertGreaterThan(0, count($errors), 'Blank postal code should trigger validation error.');
    }

    public function testPostalCodeMaxLength(): void
    {
        $address = $this->makeValidAddress();
        $address->setPostalCode('123456');

        $errors = $this->validator->validate($address);
        $this->assertGreaterThan(0, count($errors), 'Postal code >5 chars should trigger validation error.');
    }

    public function testCountryIsRequired(): void
    {
        $address = $this->makeValidAddress();
        $address->setCountry('');

        $errors = $this->validator->validate($address);
        $this->assertGreaterThan(0, count($errors), 'Blank country should trigger validation error.');
    }

    public function testLatitudeLongitudeAreOptional(): void
    {
        $address = $this->makeValidAddress();

        $this->assertNull($address->getLatitude());
        $this->assertNull($address->getLongitude());

        $address->setLatitude('43.6047');
        $address->setLongitude('3.8806');

        $this->assertEquals('43.6047', $address->getLatitude());
        $this->assertEquals('3.8806', $address->getLongitude());

        $errors = $this->validator->validate($address);
        $this->assertEquals(0, count($errors));
    }

    public function testSpacesRelation(): void
    {
        $address = $this->makeValidAddress();
        $space = new Space();
        $space->setName('Test Space');
        $space->setDescription('Description test pour espace');

        $address->addSpace($space);

        $this->assertCount(1, $address->getSpaces());
        $this->assertSame($address, $space->getAddress());

        $address->removeSpace($space);

        $this->assertCount(0, $address->getSpaces());
        $this->assertNull($space->getAddress());
    }

    public function testGettersAndSetters(): void
    {
        $address = new Address();

        $this->assertNull($address->getId());
        $this->assertNull($address->getStreet());
        $this->assertNull($address->getCity());
        $this->assertNull($address->getPostalCode());
        $this->assertNull($address->getCountry());
        $this->assertNull($address->getLatitude());
        $this->assertNull($address->getLongitude());
        $this->assertCount(0, $address->getSpaces());
    }
}
