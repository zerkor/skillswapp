<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260601110511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE availability CHANGE heure_debut heure_debut TIME NOT NULL, CHANGE heure_fin heure_fin TIME NOT NULL');
        $this->addSql('ALTER TABLE badge CHANGE condition_type condition_type VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE comment CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY `FK_C9BB43784B89032C`');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY `FK_C9BB4378A76ED395`');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C2924B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C292A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_likes RENAME INDEX idx_c9bb43784b89032c TO IDX_DED1C2924B89032C');
        $this->addSql('ALTER TABLE post_likes RENAME INDEX idx_c9bb4378a76ed395 TO IDX_DED1C292A76ED395');
        $this->addSql('ALTER TABLE review CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE session CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE session RENAME INDEX idx_d044d5d49f6c6d00 TO IDX_D044D5D486EC68D8');
        $this->addSql('ALTER TABLE session RENAME INDEX idx_d044d5d4b4ebcd20 TO IDX_D044D5D4C5697D6D');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_badge CHANGE obtained_at obtained_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE availability CHANGE heure_debut heure_debut TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', CHANGE heure_fin heure_fin TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
        $this->addSql('ALTER TABLE badge CHANGE condition_type condition_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE comment CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE post CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_DED1C2924B89032C');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_DED1C292A76ED395');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT `FK_C9BB43784B89032C` FOREIGN KEY (post_id) REFERENCES post (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT `FK_C9BB4378A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE post_likes RENAME INDEX idx_ded1c2924b89032c TO IDX_C9BB43784B89032C');
        $this->addSql('ALTER TABLE post_likes RENAME INDEX idx_ded1c292a76ed395 TO IDX_C9BB4378A76ED395');
        $this->addSql('ALTER TABLE review CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE session CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE session RENAME INDEX idx_d044d5d486ec68d8 TO IDX_D044D5D49F6C6D00');
        $this->addSql('ALTER TABLE session RENAME INDEX idx_d044d5d4c5697d6d TO IDX_D044D5D4B4EBCD20');
        $this->addSql('ALTER TABLE `user` CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_badge CHANGE obtained_at obtained_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
