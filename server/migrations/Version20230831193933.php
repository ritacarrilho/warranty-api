<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230831193933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consumer (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, first_name VARCHAR(150) NOT NULL, last_name VARCHAR(150) DEFAULT NULL, phone VARCHAR(80) DEFAULT NULL, UNIQUE INDEX UNIQ_705B3727A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, warranty_id INT NOT NULL, name VARCHAR(100) NOT NULL, path VARCHAR(200) NOT NULL, UNIQUE INDEX UNIQ_D8698A76B548B0F (path), INDEX IDX_D8698A762EC1782C (warranty_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, user_id INT NOT NULL, name VARCHAR(150) NOT NULL, brand VARCHAR(150) NOT NULL, model VARCHAR(150) DEFAULT NULL, serial_code VARCHAR(200) NOT NULL, is_active TINYINT(1) NOT NULL, purchase_date DATE DEFAULT NULL, UNIQUE INDEX UNIQ_D338D583949D2507 (serial_code), INDEX IDX_D338D58312469DE2 (category_id), INDEX IDX_D338D583A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manufacturer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, email VARCHAR(150) NOT NULL, phone VARCHAR(80) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(100) DEFAULT NULL, city VARCHAR(200) DEFAULT NULL, country VARCHAR(150) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE warranty (id INT AUTO_INCREMENT NOT NULL, equipment_id INT NOT NULL, manufacturer_id INT DEFAULT NULL, reference VARCHAR(255) NOT NULL, start_date DATE DEFAULT NULL, end_date DATE NOT NULL, UNIQUE INDEX UNIQ_88D71CF2AEA34913 (reference), INDEX IDX_88D71CF2517FE9FE (equipment_id), INDEX IDX_88D71CF2A23B42D (manufacturer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE consumer ADD CONSTRAINT FK_705B3727A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A762EC1782C FOREIGN KEY (warranty_id) REFERENCES warranty (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D58312469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D583A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE warranty ADD CONSTRAINT FK_88D71CF2517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id)');
        $this->addSql('ALTER TABLE warranty ADD CONSTRAINT FK_88D71CF2A23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumer DROP FOREIGN KEY FK_705B3727A76ED395');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A762EC1782C');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D58312469DE2');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D583A76ED395');
        $this->addSql('ALTER TABLE warranty DROP FOREIGN KEY FK_88D71CF2517FE9FE');
        $this->addSql('ALTER TABLE warranty DROP FOREIGN KEY FK_88D71CF2A23B42D');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE consumer');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE manufacturer');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE warranty');
    }
}
