<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260807113212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add core invoice fields';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice ADD invoice_number VARCHAR(100) NOT NULL, ADD invoice_date DATE NOT NULL, ADD due_date DATE DEFAULT NULL, ADD net_total NUMERIC(12, 2) NOT NULL, ADD vat_total NUMERIC(12, 2) NOT NULL, ADD gross_total NUMERIC(12, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP invoice_number, DROP invoice_date, DROP due_date, DROP net_total, DROP vat_total, DROP gross_total');
    }
}
