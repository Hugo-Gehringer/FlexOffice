<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

abstract class DatabaseTestCase extends WebTestCase
{
    protected ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        // Récupérer l'entity manager
        $this->em = static::getContainer()->get('doctrine')->getManager();

        // Créer le schéma de la base de données
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Fermer la connexion à la base de données
        if ($this->em) {
            $this->em->close();
            $this->em = null;
        }
    }
}