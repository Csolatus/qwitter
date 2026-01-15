<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260115224159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ALTER content DROP NOT NULL');
        // $this->addSql('ALTER TABLE "user" DROP is_online_visible');
        // $this->addSql('ALTER TABLE "user" DROP is_indexed');
        // $this->addSql('ALTER TABLE "user" DROP is2_faenabled');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ALTER content SET NOT NULL');
        // $this->addSql('ALTER TABLE "user" ADD is_online_visible BOOLEAN DEFAULT true NOT NULL');
        // $this->addSql('ALTER TABLE "user" ADD is_indexed BOOLEAN DEFAULT true NOT NULL');
        // $this->addSql('ALTER TABLE "user" ADD is2_faenabled BOOLEAN DEFAULT false NOT NULL');
    }
}
