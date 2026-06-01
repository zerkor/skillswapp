<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration initiale — création de toutes les tables SkillSwap.
 */
final class Version20240601000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création du schéma initial SkillSwap';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            photo VARCHAR(255) DEFAULT NULL,
            bio LONGTEXT DEFAULT NULL,
            formation VARCHAR(150) DEFAULT NULL,
            promotion VARCHAR(20) DEFAULT NULL,
            score INT DEFAULT 0 NOT NULL,
            niveau VARCHAR(20) DEFAULT \'novice\' NOT NULL,
            is_verified TINYINT(1) DEFAULT 0 NOT NULL,
            verification_token VARCHAR(100) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE skill (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            nom VARCHAR(100) NOT NULL,
            categorie VARCHAR(100) DEFAULT NULL,
            niveau SMALLINT DEFAULT 1 NOT NULL,
            type VARCHAR(10) NOT NULL,
            INDEX IDX_5E3DE477A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE availability (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            jour_semaine VARCHAR(3) NOT NULL,
            heure_debut TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\',
            heure_fin TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\',
            INDEX IDX_3FB7A2BFA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE session (
            id INT AUTO_INCREMENT NOT NULL,
            tuteur_id INT NOT NULL,
            apprenant_id INT NOT NULL,
            competence VARCHAR(100) NOT NULL,
            type VARCHAR(20) NOT NULL,
            date DATETIME NOT NULL,
            duree_minutes INT DEFAULT 60 NOT NULL,
            statut VARCHAR(20) NOT NULL,
            lieu_ou_lien VARCHAR(255) DEFAULT NULL,
            tuteur_completed TINYINT(1) DEFAULT 0 NOT NULL,
            apprenant_completed TINYINT(1) DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_D044D5D49F6C6D00 (tuteur_id),
            INDEX IDX_D044D5D4B4EBCD20 (apprenant_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE review (
            id INT AUTO_INCREMENT NOT NULL,
            session_id INT NOT NULL,
            auteur_id INT NOT NULL,
            note SMALLINT NOT NULL,
            commentaire LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_794381C6613FECDF (session_id),
            INDEX IDX_794381C660BB6FE6 (auteur_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE badge (
            id INT AUTO_INCREMENT NOT NULL,
            nom VARCHAR(100) NOT NULL,
            description VARCHAR(255) NOT NULL,
            icone VARCHAR(50) NOT NULL,
            condition_type VARCHAR(50) DEFAULT NULL,
            condition_value INT DEFAULT 1 NOT NULL,
            UNIQUE INDEX UNIQ_FEF0481D6C6E55B5 (nom),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE user_badge (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            badge_id INT NOT NULL,
            obtained_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_1C32B345A76ED395 (user_id),
            INDEX IDX_1C32B345F7A2C2FC (badge_id),
            UNIQUE INDEX user_badge_unique (user_id, badge_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE post (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            contenu LONGTEXT NOT NULL,
            image_url VARCHAR(255) DEFAULT NULL,
            competence_tag VARCHAR(100) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_5A8A6C8DA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE post_likes (
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            INDEX IDX_C9BB43784B89032C (post_id),
            INDEX IDX_C9BB4378A76ED395 (user_id),
            PRIMARY KEY(post_id, user_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE comment (
            id INT AUTO_INCREMENT NOT NULL,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            contenu LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_9474526C4B89032C (post_id),
            INDEX IDX_9474526CA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Foreign keys
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE477A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BFA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D49F6C6D00 FOREIGN KEY (tuteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4B4EBCD20 FOREIGN KEY (apprenant_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C660BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_C9BB43784B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_C9BB4378A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_C9BB43784B89032C');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_C9BB4378A76ED395');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B345A76ED395');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B345F7A2C2FC');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6613FECDF');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C660BB6FE6');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D49F6C6D00');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4B4EBCD20');
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BFA76ED395');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE477A76ED395');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE post_likes');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE user_badge');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE availability');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE `user`');
    }
}
