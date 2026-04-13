<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413092919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_client (project_id BINARY(16) NOT NULL, client_id BINARY(16) NOT NULL, INDEX IDX_D0E0EF1F166D1F9C (project_id), INDEX IDX_D0E0EF1F19EB6921 (client_id), PRIMARY KEY (project_id, client_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE project_tag (project_id BINARY(16) NOT NULL, tag_id BINARY(16) NOT NULL, INDEX IDX_91F26D60166D1F9C (project_id), INDEX IDX_91F26D60BAD26311 (tag_id), PRIMARY KEY (project_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE project_technology (project_id BINARY(16) NOT NULL, technology_id BINARY(16) NOT NULL, INDEX IDX_ECC5297F166D1F9C (project_id), INDEX IDX_ECC5297F4235D463 (technology_id), PRIMARY KEY (project_id, technology_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE project_client ADD CONSTRAINT FK_D0E0EF1F166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_client ADD CONSTRAINT FK_D0E0EF1F19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_tag ADD CONSTRAINT FK_91F26D60166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_tag ADD CONSTRAINT FK_91F26D60BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_technology ADD CONSTRAINT FK_ECC5297F166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_technology ADD CONSTRAINT FK_ECC5297F4235D463 FOREIGN KEY (technology_id) REFERENCES technology (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project CHANGE date date DATE NOT NULL, CHANGE context context VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE project_feature ADD project_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE project_feature ADD CONSTRAINT FK_89C97903166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_89C97903166D1F9C ON project_feature (project_id)');
        $this->addSql('ALTER TABLE project_highlight ADD project_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE project_highlight ADD CONSTRAINT FK_D8A49DE3166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D8A49DE3166D1F9C ON project_highlight (project_id)');
        $this->addSql('ALTER TABLE screenshot ADD project_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE screenshot ADD CONSTRAINT FK_58991E41166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_58991E41166D1F9C ON screenshot (project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_client DROP FOREIGN KEY FK_D0E0EF1F166D1F9C');
        $this->addSql('ALTER TABLE project_client DROP FOREIGN KEY FK_D0E0EF1F19EB6921');
        $this->addSql('ALTER TABLE project_tag DROP FOREIGN KEY FK_91F26D60166D1F9C');
        $this->addSql('ALTER TABLE project_tag DROP FOREIGN KEY FK_91F26D60BAD26311');
        $this->addSql('ALTER TABLE project_technology DROP FOREIGN KEY FK_ECC5297F166D1F9C');
        $this->addSql('ALTER TABLE project_technology DROP FOREIGN KEY FK_ECC5297F4235D463');
        $this->addSql('DROP TABLE project_client');
        $this->addSql('DROP TABLE project_tag');
        $this->addSql('DROP TABLE project_technology');
        $this->addSql('ALTER TABLE project CHANGE date date DATETIME NOT NULL, CHANGE context context VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE project_feature DROP FOREIGN KEY FK_89C97903166D1F9C');
        $this->addSql('DROP INDEX IDX_89C97903166D1F9C ON project_feature');
        $this->addSql('ALTER TABLE project_feature DROP project_id');
        $this->addSql('ALTER TABLE project_highlight DROP FOREIGN KEY FK_D8A49DE3166D1F9C');
        $this->addSql('DROP INDEX IDX_D8A49DE3166D1F9C ON project_highlight');
        $this->addSql('ALTER TABLE project_highlight DROP project_id');
        $this->addSql('ALTER TABLE screenshot DROP FOREIGN KEY FK_58991E41166D1F9C');
        $this->addSql('DROP INDEX IDX_58991E41166D1F9C ON screenshot');
        $this->addSql('ALTER TABLE screenshot DROP project_id');
    }
}
