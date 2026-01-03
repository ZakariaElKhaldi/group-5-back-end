<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260103160206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification_read (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, notification_id INT NOT NULL, read_at DATETIME NOT NULL, INDEX IDX_206B0A5DA76ED395 (user_id), INDEX IDX_206B0A5DEF1A9D84 (notification_id), UNIQUE INDEX user_notification_unique (user_id, notification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification_read ADD CONSTRAINT FK_206B0A5DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification_read ADD CONSTRAINT FK_206B0A5DEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)');
        $this->addSql('ALTER TABLE intervention ADD duree_reelle INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_read DROP FOREIGN KEY FK_206B0A5DA76ED395');
        $this->addSql('ALTER TABLE notification_read DROP FOREIGN KEY FK_206B0A5DEF1A9D84');
        $this->addSql('DROP TABLE notification_read');
        $this->addSql('ALTER TABLE intervention DROP duree_reelle');
    }
}
