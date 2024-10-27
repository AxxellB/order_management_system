<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024160026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_history_logs (id INT AUTO_INCREMENT NOT NULL, related_order_id INT NOT NULL, user_id INT NOT NULL, change_type VARCHAR(255) NOT NULL, old_value JSON NOT NULL, new_value JSON NOT NULL, changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FF7A224A2B1C2395 (related_order_id), INDEX IDX_FF7A224AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_history_logs ADD CONSTRAINT FK_FF7A224A2B1C2395 FOREIGN KEY (related_order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE order_history_logs ADD CONSTRAINT FK_FF7A224AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_history_logs DROP FOREIGN KEY FK_FF7A224A2B1C2395');
        $this->addSql('ALTER TABLE order_history_logs DROP FOREIGN KEY FK_FF7A224AA76ED395');
        $this->addSql('DROP TABLE order_history_logs');
    }
}
