<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630094130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, street VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, postal_code VARCHAR(5) NOT NULL, country VARCHAR(50) NOT NULL, latitude VARCHAR(255) DEFAULT NULL, longitude VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE availability (id INT AUTO_INCREMENT NOT NULL, space_id INT NOT NULL, monday TINYINT(1) NOT NULL, tuesday TINYINT(1) NOT NULL, wednesday TINYINT(1) NOT NULL, thursday TINYINT(1) NOT NULL, friday TINYINT(1) NOT NULL, saturday TINYINT(1) NOT NULL, sunday TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_3FB7A2BF23575340 (space_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE desk (id INT AUTO_INCREMENT NOT NULL, space_id INT NOT NULL, name VARCHAR(60) NOT NULL, type INT NOT NULL, description LONGTEXT NOT NULL, price_per_day DOUBLE PRECISION NOT NULL, capacity INT NOT NULL, is_available TINYINT(1) NOT NULL, INDEX IDX_56E246623575340 (space_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE desk_equipment (desk_id INT NOT NULL, equipment_id INT NOT NULL, INDEX IDX_A194E2871F9DF5E (desk_id), INDEX IDX_A194E28517FE9FE (equipment_id), PRIMARY KEY(desk_id, equipment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(60) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, guest_id INT DEFAULT NULL, desk_id INT NOT NULL, reservation_date DATETIME DEFAULT NULL, status INT NOT NULL, INDEX IDX_42C849559A4AA658 (guest_id), INDEX IDX_42C8495571F9DF5E (desk_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE space (id INT AUTO_INCREMENT NOT NULL, host_id INT NOT NULL, address_id INT NOT NULL, name VARCHAR(60) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_2972C13A1FB8D185 (host_id), INDEX IDX_2972C13AF5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, firstname VARCHAR(50) NOT NULL, lastname VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF23575340 FOREIGN KEY (space_id) REFERENCES space (id)');
        $this->addSql('ALTER TABLE desk ADD CONSTRAINT FK_56E246623575340 FOREIGN KEY (space_id) REFERENCES space (id)');
        $this->addSql('ALTER TABLE desk_equipment ADD CONSTRAINT FK_A194E2871F9DF5E FOREIGN KEY (desk_id) REFERENCES desk (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE desk_equipment ADD CONSTRAINT FK_A194E28517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849559A4AA658 FOREIGN KEY (guest_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495571F9DF5E FOREIGN KEY (desk_id) REFERENCES desk (id)');
        $this->addSql('ALTER TABLE space ADD CONSTRAINT FK_2972C13A1FB8D185 FOREIGN KEY (host_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE space ADD CONSTRAINT FK_2972C13AF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF23575340');
        $this->addSql('ALTER TABLE desk DROP FOREIGN KEY FK_56E246623575340');
        $this->addSql('ALTER TABLE desk_equipment DROP FOREIGN KEY FK_A194E2871F9DF5E');
        $this->addSql('ALTER TABLE desk_equipment DROP FOREIGN KEY FK_A194E28517FE9FE');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849559A4AA658');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495571F9DF5E');
        $this->addSql('ALTER TABLE space DROP FOREIGN KEY FK_2972C13A1FB8D185');
        $this->addSql('ALTER TABLE space DROP FOREIGN KEY FK_2972C13AF5B7AF75');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE availability');
        $this->addSql('DROP TABLE desk');
        $this->addSql('DROP TABLE desk_equipment');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE space');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
