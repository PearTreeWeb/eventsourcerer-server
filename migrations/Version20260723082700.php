<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260723082700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow mutation conditions to target a different event property';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mutation_condition ADD event_property_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE mutation_condition ADD event_property_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mutation_condition DROP event_property_id');
        $this->addSql('ALTER TABLE mutation_condition DROP event_property_type');
    }
}
