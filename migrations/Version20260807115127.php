<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260807115127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice_line_item (id INT AUTO_INCREMENT NOT NULL, position INT NOT NULL, description LONGTEXT NOT NULL, quantity NUMERIC(12, 4) DEFAULT NULL, unit_price NUMERIC(12, 4) DEFAULT NULL, net_amount NUMERIC(12, 2) NOT NULL, vat_rate NUMERIC(5, 2) DEFAULT NULL, invoice_id INT NOT NULL, INDEX IDX_F1F9275B2989F1FD (invoice_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE invoice_line_item ADD CONSTRAINT FK_F1F9275B2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_line_item DROP FOREIGN KEY FK_F1F9275B2989F1FD');
        $this->addSql('DROP TABLE invoice_line_item');
    }
}
