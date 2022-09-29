<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210715120041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE wish_list_article');
        $this->addSql('ALTER TABLE wish_list DROP FOREIGN KEY FK_5B8739BDA76ED395');
        $this->addSql('DROP INDEX UNIQ_5B8739BDA76ED395 ON wish_list');
        $this->addSql('ALTER TABLE wish_list DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wish_list_article (wish_list_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_4A0C16567294869C (article_id), INDEX IDX_4A0C1656D69F3311 (wish_list_id), PRIMARY KEY(wish_list_id, article_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE wish_list_article ADD CONSTRAINT FK_4A0C16567294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wish_list_article ADD CONSTRAINT FK_4A0C1656D69F3311 FOREIGN KEY (wish_list_id) REFERENCES wish_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wish_list ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE wish_list ADD CONSTRAINT FK_5B8739BDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5B8739BDA76ED395 ON wish_list (user_id)');
    }
}
