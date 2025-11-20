<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120102653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE measurement (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, measured_at DATETIME NOT NULL, ph DOUBLE PRECISION DEFAULT NULL, ec DOUBLE PRECISION DEFAULT NULL, water_temp DOUBLE PRECISION DEFAULT NULL, source VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, reservoir_id INTEGER NOT NULL, CONSTRAINT FK_2CE0D811CDD6B674 FOREIGN KEY (reservoir_id) REFERENCES reservoir (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2CE0D811CDD6B674 ON measurement (reservoir_id)');
        $this->addSql('CREATE TABLE reservoir (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, capacity DOUBLE PRECISION DEFAULT NULL, location VARCHAR(50) DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE measurement');
        $this->addSql('DROP TABLE reservoir');
    }
}
