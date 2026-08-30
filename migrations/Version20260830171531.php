<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260830171531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oauth_account DROP CONSTRAINT fk_6e30f9d19d86650f');
        $this->addSql('DROP INDEX idx_6e30f9d19d86650f');
        $this->addSql('ALTER TABLE oauth_account RENAME COLUMN user_id_id TO user_id');
        $this->addSql('ALTER TABLE oauth_account ADD CONSTRAINT FK_6E30F9D1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_6E30F9D1A76ED395 ON oauth_account (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oauth_account DROP CONSTRAINT FK_6E30F9D1A76ED395');
        $this->addSql('DROP INDEX IDX_6E30F9D1A76ED395');
        $this->addSql('ALTER TABLE oauth_account RENAME COLUMN user_id TO user_id_id');
        $this->addSql('ALTER TABLE oauth_account ADD CONSTRAINT fk_6e30f9d19d86650f FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6e30f9d19d86650f ON oauth_account (user_id_id)');
    }
}
