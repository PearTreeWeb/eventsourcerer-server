<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260812220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add author table and event.author_id nullable column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE author (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AUTHOR_NAME ON author (name)');
        $this->addSql('COMMENT ON COLUMN author.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE event ADD author_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN event.author_id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP author_id');
        $this->addSql('DROP TABLE author');
    }
}
