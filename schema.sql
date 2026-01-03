-- Database Schema for Group 5 Project
-- Generated on: 2026-01-01
-- MySQL / MariaDB Compatible

-- ============================================
-- TABLE DEFINITIONS
-- ============================================

CREATE TABLE piece (
    id INT AUTO_INCREMENT NOT NULL,
    fournisseur_id INT DEFAULT NULL,
    reference VARCHAR(100) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    prix_unitaire DOUBLE PRECISION NOT NULL,
    quantite_stock INT NOT NULL,
    seuil_alerte INT NOT NULL,
    emplacement VARCHAR(100) DEFAULT NULL,
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    UNIQUE INDEX UNIQ_44CA0B23AEA34913 (reference),
    INDEX IDX_44CA0B23670C757F (fournisseur_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE fournisseur (
    id INT AUTO_INCREMENT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    telephone VARCHAR(50) DEFAULT NULL,
    adresse LONGTEXT DEFAULT NULL,
    delai_livraison INT DEFAULT NULL,
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE client (
    id INT AUTO_INCREMENT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    telephone VARCHAR(50) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    adresse LONGTEXT DEFAULT NULL,
    ice VARCHAR(20) DEFAULT NULL,
    rc VARCHAR(50) DEFAULT NULL,
    patente VARCHAR(50) DEFAULT NULL,
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE panne (
    id INT AUTO_INCREMENT NOT NULL,
    machine_id INT NOT NULL,
    intervention_id INT DEFAULT NULL,
    date_declaration DATETIME NOT NULL,
    description LONGTEXT DEFAULT NULL,
    gravite VARCHAR(50) NOT NULL,
    statut VARCHAR(50) NOT NULL,
    INDEX IDX_3885B260F6B75B26 (machine_id),
    UNIQUE INDEX UNIQ_3885B2608EAE3863 (intervention_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE machine (
    id INT AUTO_INCREMENT NOT NULL,
    client_id INT DEFAULT NULL,
    reference VARCHAR(255) NOT NULL,
    modele VARCHAR(255) NOT NULL,
    marque VARCHAR(255) NOT NULL,
    type VARCHAR(255) NOT NULL,
    date_acquisition DATE NOT NULL,
    statut VARCHAR(50) NOT NULL,
    UNIQUE INDEX UNIQ_1505DF84AEA34913 (reference),
    INDEX IDX_1505DF8419EB6921 (client_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE user (
    id INT AUTO_INCREMENT NOT NULL,
    email VARCHAR(180) NOT NULL,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE mouvement_stock (
    id INT AUTO_INCREMENT NOT NULL,
    piece_id INT NOT NULL,
    type VARCHAR(20) NOT NULL,
    quantite INT NOT NULL,
    quantite_avant INT NOT NULL,
    quantite_apres INT NOT NULL,
    motif VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_61E2C8EBC40FCFA8 (piece_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE piece_intervention (
    id INT AUTO_INCREMENT NOT NULL,
    piece_id INT NOT NULL,
    intervention_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire_applique DOUBLE PRECISION NOT NULL,
    date_utilisation DATETIME NOT NULL,
    INDEX IDX_20322619C40FCFA8 (piece_id),
    INDEX IDX_203226198EAE3863 (intervention_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE intervention_log (
    id INT AUTO_INCREMENT NOT NULL,
    intervention_id INT NOT NULL,
    user_id INT NOT NULL,
    message LONGTEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX IDX_30DE9E8C8EAE3863 (intervention_id),
    INDEX IDX_30DE9E8CA76ED395 (user_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE intervention (
    id INT AUTO_INCREMENT NOT NULL,
    machine_id INT NOT NULL,
    technicien_id INT DEFAULT NULL,
    type VARCHAR(50) NOT NULL,
    priorite VARCHAR(50) NOT NULL,
    statut VARCHAR(50) NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin_prevue DATETIME DEFAULT NULL,
    date_fin_reelle DATETIME DEFAULT NULL,
    duree VARCHAR(255) DEFAULT NULL,
    description LONGTEXT DEFAULT NULL,
    resolution LONGTEXT DEFAULT NULL,
    cout_main_oeuvre DOUBLE PRECISION DEFAULT NULL,
    cout_pieces DOUBLE PRECISION DEFAULT NULL,
    cout_total DOUBLE PRECISION DEFAULT NULL,
    taux_horaire_applique DOUBLE PRECISION DEFAULT NULL,
    confirmation_technicien TINYINT(1) NOT NULL,
    confirmation_technicien_at DATETIME DEFAULT NULL,
    confirmation_client TINYINT(1) NOT NULL,
    confirmation_client_at DATETIME DEFAULT NULL,
    signature_client LONGTEXT DEFAULT NULL,
    signer_nom VARCHAR(255) DEFAULT NULL,
    INDEX IDX_D11814ABF6B75B26 (machine_id),
    INDEX IDX_D11814AB13457256 (technicien_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE technicien (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    specialite VARCHAR(255) NOT NULL,
    taux_horaire DOUBLE PRECISION NOT NULL,
    statut VARCHAR(50) NOT NULL,
    UNIQUE INDEX UNIQ_96282C4CA76ED395 (user_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE messenger_messages (
    id BIGINT AUTO_INCREMENT NOT NULL,
    body LONGTEXT NOT NULL,
    headers LONGTEXT NOT NULL,
    queue_name VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_75EA56E0FB7336F0 (queue_name),
    INDEX IDX_75EA56E0E3BD61CE (available_at),
    INDEX IDX_75EA56E016BA31DB (delivered_at),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- ============================================
-- FOREIGN KEY CONSTRAINTS
-- ============================================

ALTER TABLE piece ADD CONSTRAINT FK_44CA0B23670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id);

ALTER TABLE panne ADD CONSTRAINT FK_3885B260F6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id);
ALTER TABLE panne ADD CONSTRAINT FK_3885B2608EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id);

ALTER TABLE machine ADD CONSTRAINT FK_1505DF8419EB6921 FOREIGN KEY (client_id) REFERENCES client (id);

ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EBC40FCFA8 FOREIGN KEY (piece_id) REFERENCES piece (id);

ALTER TABLE piece_intervention ADD CONSTRAINT FK_20322619C40FCFA8 FOREIGN KEY (piece_id) REFERENCES piece (id);
ALTER TABLE piece_intervention ADD CONSTRAINT FK_203226198EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id);

ALTER TABLE intervention_log ADD CONSTRAINT FK_30DE9E8C8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id);
ALTER TABLE intervention_log ADD CONSTRAINT FK_30DE9E8CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id);

ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABF6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id);
ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB13457256 FOREIGN KEY (technicien_id) REFERENCES technicien (id);

ALTER TABLE technicien ADD CONSTRAINT FK_96282C4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id);
