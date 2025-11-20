<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120115107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create journal_entry table for culture journal entries';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE journal_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL, photo_url VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, reservoir_id INTEGER NOT NULL, CONSTRAINT FK_C8FAAE5ACDD6B674 FOREIGN KEY (reservoir_id) REFERENCES reservoir (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C8FAAE5ACDD6B674 ON journal_entry (reservoir_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE journal_entry');
    }
}
