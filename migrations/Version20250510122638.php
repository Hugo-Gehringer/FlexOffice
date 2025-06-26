<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250510122638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE availability DROP INDEX IDX_3FB7A2BF23575340, ADD UNIQUE INDEX UNIQ_3FB7A2BF23575340 (space_id)');
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF71F9DF5E');
        $this->addSql('DROP INDEX IDX_3FB7A2BF71F9DF5E ON availability');
        $this->addSql('ALTER TABLE availability DROP desk_id, CHANGE space_id space_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE availability DROP INDEX UNIQ_3FB7A2BF23575340, ADD INDEX IDX_3FB7A2BF23575340 (space_id)');
        $this->addSql('ALTER TABLE availability ADD desk_id INT DEFAULT NULL, CHANGE space_id space_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF71F9DF5E FOREIGN KEY (desk_id) REFERENCES desk (id)');
        $this->addSql('CREATE INDEX IDX_3FB7A2BF71F9DF5E ON availability (desk_id)');
    }
}
