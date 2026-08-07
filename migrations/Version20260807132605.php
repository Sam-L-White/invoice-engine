<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260807132605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add invoice document and link invoices to source documents';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice_document (id INT AUTO_INCREMENT NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, storage_path VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE invoice ADD document_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744C33F7837 FOREIGN KEY (document_id) REFERENCES invoice_document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_90651744C33F7837 ON invoice (document_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744C33F7837');
        $this->addSql('DROP INDEX UNIQ_90651744C33F7837 ON invoice');
        $this->addSql('ALTER TABLE invoice DROP document_id');
        $this->addSql('DROP TABLE invoice_document');
    }
}
