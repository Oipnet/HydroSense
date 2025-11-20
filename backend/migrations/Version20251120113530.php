<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120113530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create alert table and add culture_profile_id to farm table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alert (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(50) NOT NULL, severity VARCHAR(20) NOT NULL, message CLOB NOT NULL, measured_value DOUBLE PRECISION NOT NULL, expected_min DOUBLE PRECISION DEFAULT NULL, expected_max DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, resolved_at DATETIME DEFAULT NULL, reservoir_id INTEGER NOT NULL, measurement_id INTEGER NOT NULL, CONSTRAINT FK_17FD46C1CDD6B674 FOREIGN KEY (reservoir_id) REFERENCES reservoir (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_17FD46C1924EA134 FOREIGN KEY (measurement_id) REFERENCES measurement (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_17FD46C1CDD6B674 ON alert (reservoir_id)');
        $this->addSql('CREATE INDEX IDX_17FD46C1924EA134 ON alert (measurement_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__farm AS SELECT id, name, created_at, updated_at, owner_id FROM farm');
        $this->addSql('DROP TABLE farm');
        $this->addSql('CREATE TABLE farm (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, owner_id INTEGER NOT NULL, culture_profile_id INTEGER DEFAULT NULL, CONSTRAINT FK_5816D0457E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5816D04528AC45D3 FOREIGN KEY (culture_profile_id) REFERENCES culture_profile (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO farm (id, name, created_at, updated_at, owner_id) SELECT id, name, created_at, updated_at, owner_id FROM __temp__farm');
        $this->addSql('DROP TABLE __temp__farm');
        $this->addSql('CREATE INDEX IDX_5816D0457E3C61F9 ON farm (owner_id)');
        $this->addSql('CREATE INDEX IDX_5816D04528AC45D3 ON farm (culture_profile_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE alert');
        $this->addSql('CREATE TEMPORARY TABLE __temp__farm AS SELECT id, name, created_at, updated_at, owner_id FROM farm');
        $this->addSql('DROP TABLE farm');
        $this->addSql('CREATE TABLE farm (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, owner_id INTEGER NOT NULL, CONSTRAINT FK_5816D0457E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO farm (id, name, created_at, updated_at, owner_id) SELECT id, name, created_at, updated_at, owner_id FROM __temp__farm');
        $this->addSql('DROP TABLE __temp__farm');
        $this->addSql('CREATE INDEX IDX_5816D0457E3C61F9 ON farm (owner_id)');
    }
}
