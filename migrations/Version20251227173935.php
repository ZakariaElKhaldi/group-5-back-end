<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251227173935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, machine_id INT NOT NULL, technicien_id INT DEFAULT NULL, type VARCHAR(50) NOT NULL, priorite VARCHAR(50) NOT NULL, statut VARCHAR(50) NOT NULL, date_debut DATETIME NOT NULL, date_fin_prevue DATETIME DEFAULT NULL, date_fin_reelle DATETIME DEFAULT NULL, duree VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, resolution LONGTEXT DEFAULT NULL, cout_main_oeuvre DOUBLE PRECISION DEFAULT NULL, cout_pieces DOUBLE PRECISION DEFAULT NULL, cout_total DOUBLE PRECISION DEFAULT NULL, INDEX IDX_D11814ABF6B75B26 (machine_id), INDEX IDX_D11814AB13457256 (technicien_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE machine (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(255) NOT NULL, modele VARCHAR(255) NOT NULL, marque VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, date_acquisition DATE NOT NULL, statut VARCHAR(50) NOT NULL, client_nom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1505DF84AEA34913 (reference), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE panne (id INT AUTO_INCREMENT NOT NULL, machine_id INT NOT NULL, date_declaration DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, gravite VARCHAR(50) NOT NULL, INDEX IDX_3885B260F6B75B26 (machine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technicien (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, specialite VARCHAR(255) NOT NULL, taux_horaire DOUBLE PRECISION NOT NULL, statut VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_96282C4CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB13457256 FOREIGN KEY (technicien_id) REFERENCES technicien (id)');
        $this->addSql('ALTER TABLE panne ADD CONSTRAINT FK_3885B260F6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
        $this->addSql('ALTER TABLE technicien ADD CONSTRAINT FK_96282C4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABF6B75B26');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB13457256');
        $this->addSql('ALTER TABLE panne DROP FOREIGN KEY FK_3885B260F6B75B26');
        $this->addSql('ALTER TABLE technicien DROP FOREIGN KEY FK_96282C4CA76ED395');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE machine');
        $this->addSql('DROP TABLE panne');
        $this->addSql('DROP TABLE technicien');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
