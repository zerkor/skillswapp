<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration v1.1 — Retours comité client 02/06/2026.
 *
 * Changements :
 *  - Ajout colonne `pseudo` (VARCHAR 50, unique) sur `user`
 *  - Nouvelle table `education` (parcours académique séparé des compétences)
 *  - Ajout colonne `reaction_counts` (JSON) sur `post` pour les réactions multi-types
 */
final class Version20260602000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'v1.1 : pseudo utilisateur + table education + réactions feed';
    }

    public function up(Schema $schema): void
    {
        // Pseudonyme public sur l'utilisateur
        $this->addSql("ALTER TABLE `user` ADD pseudo VARCHAR(50) DEFAULT NULL");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_8D93D64986CC499D ON `user` (pseudo)");

        // Table Education — parcours académique (distinct des compétences)
        $this->addSql('CREATE TABLE education (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            diplome VARCHAR(150) NOT NULL,
            etablissement VARCHAR(200) DEFAULT NULL,
            niveau VARCHAR(10) DEFAULT NULL,
            annee VARCHAR(4) DEFAULT NULL,
            INDEX IDX_DB0A5ED2A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');

        // Réactions multi-types sur les posts (JSON stocké en colonne)
        $this->addSql("ALTER TABLE post ADD reaction_counts JSON DEFAULT NULL COMMENT 'JSON: {like:0, heart:0, idea:0, celebrate:0}'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2A76ED395');
        $this->addSql('DROP TABLE education');
        $this->addSql('DROP INDEX UNIQ_8D93D64986CC499D ON `user`');
        $this->addSql('ALTER TABLE `user` DROP pseudo');
        $this->addSql('ALTER TABLE post DROP reaction_counts');
    }
}
