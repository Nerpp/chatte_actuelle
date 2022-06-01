<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220524074956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reporting');
        $this->addSql('ALTER TABLE comments ADD reported TINYINT(1) DEFAULT NULL, ADD moderated TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reporting (id INT AUTO_INCREMENT NOT NULL, fk_comments_id INT NOT NULL, fk_user_id INT NOT NULL, report INT NOT NULL, INDEX IDX_BD7CFA9F5741EEB9 (fk_user_id), INDEX IDX_BD7CFA9FAC139B2 (fk_comments_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reporting ADD CONSTRAINT FK_BD7CFA9F5741EEB9 FOREIGN KEY (fk_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reporting ADD CONSTRAINT FK_BD7CFA9FAC139B2 FOREIGN KEY (fk_comments_id) REFERENCES comments (id)');
        $this->addSql('ALTER TABLE comments DROP reported, DROP moderated');
    }
}
