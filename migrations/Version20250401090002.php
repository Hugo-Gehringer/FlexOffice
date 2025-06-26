<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401090002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change openingTime and closingTime from string to time type';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE availability CHANGE opening_time opening_time TIME DEFAULT NULL, CHANGE closing_time closing_time TIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE availability CHANGE opening_time opening_time VARCHAR(10) DEFAULT NULL, CHANGE closing_time closing_time VARCHAR(10) DEFAULT NULL');
    }
}
