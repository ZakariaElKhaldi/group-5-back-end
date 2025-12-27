<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251227180201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, telephone VARCHAR(50) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE intervention ADD taux_horaire_applique DOUBLE PRECISION DEFAULT NULL, ADD confirmation_technicien TINYINT(1) NOT NULL, ADD confirmation_technicien_at DATETIME DEFAULT NULL, ADD confirmation_client TINYINT(1) NOT NULL, ADD confirmation_client_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE machine ADD client_id INT DEFAULT NULL, DROP client_nom');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF8419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_1505DF8419EB6921 ON machine (client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE machine DROP FOREIGN KEY FK_1505DF8419EB6921');
        $this->addSql('DROP TABLE client');
        $this->addSql('ALTER TABLE intervention DROP taux_horaire_applique, DROP confirmation_technicien, DROP confirmation_technicien_at, DROP confirmation_client, DROP confirmation_client_at');
        $this->addSql('DROP INDEX IDX_1505DF8419EB6921 ON machine');
        $this->addSql('ALTER TABLE machine ADD client_nom VARCHAR(255) NOT NULL, DROP client_id');
    }
}
