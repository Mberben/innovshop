<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261027150644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_image ADD product_option_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F03C964ABE2 FOREIGN KEY (product_option_id) REFERENCES product_option (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_64617F03C964ABE2 ON product_image (product_option_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F03C964ABE2');
        $this->addSql('DROP INDEX IDX_64617F03C964ABE2 ON product_image');
        $this->addSql('ALTER TABLE product_image DROP product_option_id');
    }
}
