<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();

        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
    }
}
