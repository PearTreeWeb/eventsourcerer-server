<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260817092200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add forename, surname and company_name columns to user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD forename VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE "user" ADD surname VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE "user" ADD company_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER forename DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER surname DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP forename');
        $this->addSql('ALTER TABLE "user" DROP surname');
        $this->addSql('ALTER TABLE "user" DROP company_name');
    }
}
