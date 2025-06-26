<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401090001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update Availability entity to be linked only to Space';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF71F9DF5E');
        $this->addSql('DROP INDEX IDX_3FB7A2BF71F9DF5E ON availability');
        $this->addSql('ALTER TABLE availability DROP desk_id');
        $this->addSql('ALTER TABLE availability MODIFY space_id INT NOT NULL');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF23575340 FOREIGN KEY (space_id) REFERENCES space (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3FB7A2BF23575340 ON availability (space_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_3FB7A2BF23575340 ON availability');
        $this->addSql('ALTER TABLE availability CHANGE space_id space_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE availability ADD desk_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF71F9DF5E FOREIGN KEY (desk_id) REFERENCES desk (id)');
        $this->addSql('CREATE INDEX IDX_3FB7A2BF71F9DF5E ON availability (desk_id)');
    }
}
