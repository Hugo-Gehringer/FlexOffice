<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Availability entity for spaces and desks';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE availability (id INT AUTO_INCREMENT NOT NULL, space_id INT DEFAULT NULL, desk_id INT DEFAULT NULL, monday TINYINT(1) NOT NULL, tuesday TINYINT(1) NOT NULL, wednesday TINYINT(1) NOT NULL, thursday TINYINT(1) NOT NULL, friday TINYINT(1) NOT NULL, saturday TINYINT(1) NOT NULL, sunday TINYINT(1) NOT NULL, opening_time VARCHAR(10) DEFAULT NULL, closing_time VARCHAR(10) DEFAULT NULL, INDEX IDX_3FB7A2BF23575340 (space_id), INDEX IDX_3FB7A2BF71F9DF5E (desk_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF23575340 FOREIGN KEY (space_id) REFERENCES space (id)');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF71F9DF5E FOREIGN KEY (desk_id) REFERENCES desk (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF23575340');
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF71F9DF5E');
        $this->addSql('DROP TABLE availability');
    }
}
