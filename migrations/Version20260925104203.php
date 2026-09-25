<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260925104203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_rule DROP CONSTRAINT fk_b001cacab6e62efa');
        $this->addSql('ALTER TABLE access_rule ADD CONSTRAINT FK_B001CACAB6E62EFA FOREIGN KEY (attribute_id) REFERENCES attribute (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE attribute_value DROP CONSTRAINT fk_fe4fbb82b6e62efa');
        $this->addSql('ALTER TABLE attribute_value ADD CONSTRAINT FK_FE4FBB82B6E62EFA FOREIGN KEY (attribute_id) REFERENCES attribute (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE cv DROP CONSTRAINT fk_b66ffe9291bd8781');
        $this->addSql('ALTER TABLE cv DROP CONSTRAINT fk_b66ffe92dd842e46');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE9291BD8781 FOREIGN KEY (candidate_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE92DD842E46 FOREIGN KEY (position_id) REFERENCES position (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "like" ALTER recruiter_id DROP NOT NULL');
        $this->addSql('ALTER TABLE position_attribute DROP CONSTRAINT fk_af5bee86b6e62efa');
        $this->addSql('ALTER TABLE position_attribute ADD CONSTRAINT FK_AF5BEE86B6E62EFA FOREIGN KEY (attribute_id) REFERENCES attribute (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT fk_2fb3d0ee91bd8781');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE91BD8781 FOREIGN KEY (candidate_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_rule DROP CONSTRAINT FK_B001CACAB6E62EFA');
        $this->addSql('ALTER TABLE access_rule ADD CONSTRAINT fk_b001cacab6e62efa FOREIGN KEY (attribute_id) REFERENCES attribute (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE attribute_value DROP CONSTRAINT FK_FE4FBB82B6E62EFA');
        $this->addSql('ALTER TABLE attribute_value ADD CONSTRAINT fk_fe4fbb82b6e62efa FOREIGN KEY (attribute_id) REFERENCES attribute (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cv DROP CONSTRAINT FK_B66FFE92DD842E46');
        $this->addSql('ALTER TABLE cv DROP CONSTRAINT FK_B66FFE9291BD8781');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT fk_b66ffe92dd842e46 FOREIGN KEY (position_id) REFERENCES "position" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT fk_b66ffe9291bd8781 FOREIGN KEY (candidate_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "like" ALTER recruiter_id SET NOT NULL');
        $this->addSql('ALTER TABLE position_attribute DROP CONSTRAINT FK_AF5BEE86B6E62EFA');
        $this->addSql('ALTER TABLE position_attribute ADD CONSTRAINT fk_af5bee86b6e62efa FOREIGN KEY (attribute_id) REFERENCES attribute (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE91BD8781');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT fk_2fb3d0ee91bd8781 FOREIGN KEY (candidate_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
