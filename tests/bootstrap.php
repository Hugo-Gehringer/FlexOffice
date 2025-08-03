<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Charge les variables d'environnement de test
if (file_exists(dirname(__DIR__) . '/.env.test')) {
    (new Dotenv())->usePutenv()->bootEnv(dirname(__DIR__) . '/.env.test');
}

// Vérification sécurisée de la variable DATABASE_URL
if (isset($_ENV['DATABASE_URL']) && $_ENV['DATABASE_URL'] === 'sqlite:///:memory:') {
    $kernel = new \App\Kernel('test', true);
    $kernel->boot();

    $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
    $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

    if (!empty($metadata)) {
        $schemaTool->createSchema($metadata);
    }
}