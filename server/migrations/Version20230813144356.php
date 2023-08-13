<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230813144356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumer ADD user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE consumer ADD CONSTRAINT FK_705B37279D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_705B37279D86650F ON consumer (user_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumer DROP FOREIGN KEY FK_705B37279D86650F');
        $this->addSql('DROP INDEX UNIQ_705B37279D86650F ON consumer');
        $this->addSql('ALTER TABLE consumer DROP user_id_id');
    }
}
