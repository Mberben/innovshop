<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261027101412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(30) NOT NULL, status VARCHAR(255) NOT NULL, total INT NOT NULL, shipping_name VARCHAR(200) NOT NULL, shipping_adress VARCHAR(255) NOT NULL, shipping_postal_code VARCHAR(10) NOT NULL, shipping_city VARCHAR(100) NOT NULL, shipping_phone VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_line (id INT AUTO_INCREMENT NOT NULL, product_name VARCHAR(150) NOT NULL, option_label VARCHAR(150) DEFAULT NULL, price INT NOT NULL, customer_order_id INT NOT NULL, product_id INT DEFAULT NULL, product_option_id INT DEFAULT NULL, INDEX IDX_9CE58EE1A15A2E17 (customer_order_id), INDEX IDX_9CE58EE14584665A (product_id), INDEX IDX_9CE58EE1C964ABE2 (product_option_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE1A15A2E17 FOREIGN KEY (customer_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE14584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE1C964ABE2 FOREIGN KEY (product_option_id) REFERENCES product_option (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE order_line DROP FOREIGN KEY FK_9CE58EE1A15A2E17');
        $this->addSql('ALTER TABLE order_line DROP FOREIGN KEY FK_9CE58EE14584665A');
        $this->addSql('ALTER TABLE order_line DROP FOREIGN KEY FK_9CE58EE1C964ABE2');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_line');
    }
}
