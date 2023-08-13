<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230813143555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consumer (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(150) NOT NULL, last_name VARCHAR(150) NOT NULL, phone VARCHAR(80) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, warranty_id_id INT NOT NULL, path VARCHAR(100) NOT NULL, INDEX IDX_D8698A765C937B42 (warranty_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, category_id_id INT NOT NULL, name VARCHAR(150) NOT NULL, brand VARCHAR(150) NOT NULL, model VARCHAR(150) DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, serial_code VARCHAR(200) NOT NULL, purchase_date DATE DEFAULT NULL, INDEX IDX_D338D5839777D11E (category_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manufacturer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, email VARCHAR(150) NOT NULL, phone VARCHAR(80) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(100) DEFAULT NULL, city VARCHAR(200) DEFAULT NULL, country VARCHAR(150) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE warranty (id INT AUTO_INCREMENT NOT NULL, equipment_id_id INT NOT NULL, manufacturer_id_id INT DEFAULT NULL, reference VARCHAR(255) NOT NULL, start_date DATE DEFAULT NULL, end_date DATE NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_88D71CF2961DBFB3 (equipment_id_id), INDEX IDX_88D71CF2741A0A47 (manufacturer_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A765C937B42 FOREIGN KEY (warranty_id_id) REFERENCES warranty (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D5839777D11E FOREIGN KEY (category_id_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE warranty ADD CONSTRAINT FK_88D71CF2961DBFB3 FOREIGN KEY (equipment_id_id) REFERENCES equipment (id)');
        $this->addSql('ALTER TABLE warranty ADD CONSTRAINT FK_88D71CF2741A0A47 FOREIGN KEY (manufacturer_id_id) REFERENCES manufacturer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A765C937B42');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D5839777D11E');
        $this->addSql('ALTER TABLE warranty DROP FOREIGN KEY FK_88D71CF2961DBFB3');
        $this->addSql('ALTER TABLE warranty DROP FOREIGN KEY FK_88D71CF2741A0A47');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE consumer');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE manufacturer');
        $this->addSql('DROP TABLE warranty');
    }
}
