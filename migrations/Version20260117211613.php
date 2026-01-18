<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117211613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application (id CHAR(36) NOT NULL, applied_at DATETIME NOT NULL, candidate_id CHAR(36) NOT NULL, job_offer_id CHAR(36) NOT NULL, INDEX IDX_A45BDDC191BD8781 (candidate_id), INDEX IDX_A45BDDC13481D195 (job_offer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE candidate (id CHAR(36) NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, image_file_path VARCHAR(255) NOT NULL, cvfile_path VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE interview (id CHAR(36) NOT NULL, start_date DATETIME NOT NULL, minutes INT NOT NULL, status VARCHAR(255) NOT NULL, candidate_id CHAR(36) NOT NULL, job_offer_id CHAR(36) DEFAULT NULL, INDEX IDX_CF1D3C3491BD8781 (candidate_id), INDEX IDX_CF1D3C343481D195 (job_offer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE job_category (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, job_offer_id CHAR(36) NOT NULL, INDEX IDX_610BBCBA12469DE2 (category_id), INDEX IDX_610BBCBA3481D195 (job_offer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE job_offer (id CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, content JSON NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL, user_id CHAR(36) NOT NULL, INDEX IDX_288A3A4EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL, name VARCHAR(150) NOT NULL, email VARCHAR(255) NOT NULL, image_path VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC191BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC13481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE interview ADD CONSTRAINT FK_CF1D3C3491BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id)');
        $this->addSql('ALTER TABLE interview ADD CONSTRAINT FK_CF1D3C343481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE job_category ADD CONSTRAINT FK_610BBCBA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE job_category ADD CONSTRAINT FK_610BBCBA3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC191BD8781');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC13481D195');
        $this->addSql('ALTER TABLE interview DROP FOREIGN KEY FK_CF1D3C3491BD8781');
        $this->addSql('ALTER TABLE interview DROP FOREIGN KEY FK_CF1D3C343481D195');
        $this->addSql('ALTER TABLE job_category DROP FOREIGN KEY FK_610BBCBA12469DE2');
        $this->addSql('ALTER TABLE job_category DROP FOREIGN KEY FK_610BBCBA3481D195');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EA76ED395');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE candidate');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE interview');
        $this->addSql('DROP TABLE job_category');
        $this->addSql('DROP TABLE job_offer');
        $this->addSql('DROP TABLE user');
    }
}
