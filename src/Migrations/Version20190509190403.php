<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190509190403 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cheese_listing ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE cheese_listing ADD CONSTRAINT FK_356577D47E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_356577D47E3C61F9 ON cheese_listing (owner_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cheese_listing DROP FOREIGN KEY FK_356577D47E3C61F9');
        $this->addSql('DROP INDEX IDX_356577D47E3C61F9 ON cheese_listing');
        $this->addSql('ALTER TABLE cheese_listing DROP owner_id');
    }
}
