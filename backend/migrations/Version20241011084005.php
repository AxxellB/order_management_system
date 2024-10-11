<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011084005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE addresses ADD order_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE addresses ADD CONSTRAINT FK_6FCA75163DA206A5 FOREIGN KEY (order_entity_id) REFERENCES orders (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6FCA75163DA206A5 ON addresses (order_entity_id)');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEEBF23851');
        $this->addSql('DROP INDEX UNIQ_E52FFDEEEBF23851 ON orders');
        $this->addSql('ALTER TABLE orders DROP delivery_address_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE addresses DROP FOREIGN KEY FK_6FCA75163DA206A5');
        $this->addSql('DROP INDEX UNIQ_6FCA75163DA206A5 ON addresses');
        $this->addSql('ALTER TABLE addresses DROP order_entity_id');
        $this->addSql('ALTER TABLE orders ADD delivery_address_id INT NOT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEEBF23851 FOREIGN KEY (delivery_address_id) REFERENCES addresses (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E52FFDEEEBF23851 ON orders (delivery_address_id)');
    }
}
