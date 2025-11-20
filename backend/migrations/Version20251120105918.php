<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120105918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE farm (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, owner_id INTEGER NOT NULL, CONSTRAINT FK_5816D0457E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5816D0457E3C61F9 ON farm (owner_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__reservoir AS SELECT id, name, description, location FROM reservoir');
        $this->addSql('DROP TABLE reservoir');
        $this->addSql('CREATE TABLE reservoir (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, location VARCHAR(50) DEFAULT NULL, volume_liters DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, farm_id INTEGER NOT NULL, CONSTRAINT FK_A117057165FCFA0D FOREIGN KEY (farm_id) REFERENCES farm (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO reservoir (id, name, description, location) SELECT id, name, description, location FROM __temp__reservoir');
        $this->addSql('DROP TABLE __temp__reservoir');
        $this->addSql('CREATE INDEX IDX_A117057165FCFA0D ON reservoir (farm_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE farm');
        $this->addSql('CREATE TEMPORARY TABLE __temp__reservoir AS SELECT id, name, description, location FROM reservoir');
        $this->addSql('DROP TABLE reservoir');
        $this->addSql('CREATE TABLE reservoir (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, location VARCHAR(50) DEFAULT NULL, capacity DOUBLE PRECISION DEFAULT NULL)');
        $this->addSql('INSERT INTO reservoir (id, name, description, location) SELECT id, name, description, location FROM __temp__reservoir');
        $this->addSql('DROP TABLE __temp__reservoir');
    }
}
