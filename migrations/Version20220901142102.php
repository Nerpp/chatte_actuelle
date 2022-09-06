<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220901142102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AAA334807');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25A76ED395');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP INDEX IDX_5F9E962AAA334807 ON comments');
        $this->addSql('ALTER TABLE comments DROP answer_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, answer VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, INDEX IDX_DADD4A25A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comments ADD answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AAA334807 FOREIGN KEY (answer_id) REFERENCES answer (id)');
        $this->addSql('CREATE INDEX IDX_5F9E962AAA334807 ON comments (answer_id)');
    }
}
