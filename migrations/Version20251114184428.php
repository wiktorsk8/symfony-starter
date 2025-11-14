<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114184428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE short_links ADD access_counter INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE short_links ADD access_limit INT DEFAULT NULL');
        $this->addSql('ALTER TABLE short_links ADD is_white_listed BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE short_links ADD click_counter INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE short_links DROP access_counter');
        $this->addSql('ALTER TABLE short_links DROP access_limit');
        $this->addSql('ALTER TABLE short_links DROP is_white_listed');
        $this->addSql('ALTER TABLE short_links DROP click_counter');
    }
}
