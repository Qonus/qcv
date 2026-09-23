<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260923111507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_rule ADD value_numeric NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE access_rule ADD value_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE access_rule ADD value_string VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE access_rule ADD value_boolean BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE access_rule ADD value_duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE access_rule ADD value_option_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE access_rule ALTER match_type DROP NOT NULL');
        $this->addSql('ALTER TABLE access_rule ADD CONSTRAINT FK_B001CACAE25618D9 FOREIGN KEY (value_option_id) REFERENCES attribute_option (id)');
        $this->addSql('CREATE INDEX IDX_B001CACAE25618D9 ON access_rule (value_option_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_rule DROP CONSTRAINT FK_B001CACAE25618D9');
        $this->addSql('DROP INDEX IDX_B001CACAE25618D9');
        $this->addSql('ALTER TABLE access_rule DROP value_numeric');
        $this->addSql('ALTER TABLE access_rule DROP value_date');
        $this->addSql('ALTER TABLE access_rule DROP value_string');
        $this->addSql('ALTER TABLE access_rule DROP value_boolean');
        $this->addSql('ALTER TABLE access_rule DROP value_duration');
        $this->addSql('ALTER TABLE access_rule DROP value_option_id');
        $this->addSql('ALTER TABLE access_rule ALTER match_type SET NOT NULL');
    }
}
