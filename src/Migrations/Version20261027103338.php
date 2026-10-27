<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261027103338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_line DROP FOREIGN KEY `FK_9CE58EE14584665A`');
        $this->addSql('ALTER TABLE order_line DROP FOREIGN KEY `FK_9CE58EE1C964ABE2`');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE14584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE1C964ABE2 FOREIGN KEY (product_option_id) REFERENCES product_option (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user CHANGE postal_code postal_code VARCHAR(10) DEFAULT NULL, CHANGE monthly_spending_goal monthly_spending_goal INT DEFAULT NULL, CHANGE fist_name first_name VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_line DROP FOREIGN KEY FK_9CE58EE14584665A');
        $this->addSql('ALTER TABLE order_line DROP FOREIGN KEY FK_9CE58EE1C964ABE2');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT `FK_9CE58EE14584665A` FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT `FK_9CE58EE1C964ABE2` FOREIGN KEY (product_option_id) REFERENCES product_option (id)');
        $this->addSql('ALTER TABLE user CHANGE postal_code postal_code VARCHAR(10) NOT NULL, CHANGE monthly_spending_goal monthly_spending_goal NUMERIC(10, 2) DEFAULT NULL, CHANGE first_name fist_name VARCHAR(100) NOT NULL');
    }
}
