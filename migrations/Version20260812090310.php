<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260812090310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add state to invoice_document';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE invoice_document ADD state VARCHAR(255) DEFAULT NULL'
        );

        $this->addSql(
            "UPDATE invoice_document SET state = 'uploaded'"
        );

        $this->addSql(
            'ALTER TABLE invoice_document MODIFY state VARCHAR(255) NOT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_document DROP state');
    }
}
