<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413075118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE project CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE project_feature CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE project_highlight CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE screenshot CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE technology CHANGE id id BINARY(16) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE project CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE project_feature CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE project_highlight CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE screenshot CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE technology CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
