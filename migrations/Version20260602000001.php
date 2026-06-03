<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajout de la colonne condition_label sur badge pour les tooltips.
 */
final class Version20260602000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Badges : ajout colonne condition_label pour les tooltips';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE badge ADD condition_label VARCHAR(255) DEFAULT NULL AFTER description");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE badge DROP condition_label');
    }
}
