<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220401212046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE expense_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE expense_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE expense (id INT NOT NULL, expense_type_id INT NOT NULL, description VARCHAR(255) NOT NULL, value DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D3A8DA6A857C7A9 ON expense (expense_type_id)');
        $this->addSql('CREATE TABLE expense_type (id INT NOT NULL, name VARCHAR(64) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA6A857C7A9 FOREIGN KEY (expense_type_id) REFERENCES expense_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE expense DROP CONSTRAINT FK_2D3A8DA6A857C7A9');
        $this->addSql('DROP SEQUENCE expense_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE expense_type_id_seq CASCADE');
        $this->addSql('DROP TABLE expense');
        $this->addSql('DROP TABLE expense_type');
    }
}
