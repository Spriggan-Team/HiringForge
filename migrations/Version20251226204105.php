<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251226204105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Account (id CHAR(36) NOT NULL, firstanme VARCHAR(150) NOT NULL, lastname VARCHAR(150) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B28B6F38E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE post ADD account_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_FAB8C3B39B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)');
        $this->addSql('CREATE INDEX IDX_FAB8C3B39B6B5FBA ON post (account_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE Account');
        $this->addSql('ALTER TABLE Post DROP FOREIGN KEY FK_FAB8C3B39B6B5FBA');
        $this->addSql('DROP INDEX IDX_FAB8C3B39B6B5FBA ON Post');
        $this->addSql('ALTER TABLE Post DROP account_id');
    }
}
