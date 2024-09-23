<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240806154559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_list ADD user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task_list ADD CONSTRAINT FK_377B6C639D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_377B6C639D86650F ON task_list (user_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_list DROP FOREIGN KEY FK_377B6C639D86650F');
        $this->addSql('DROP INDEX IDX_377B6C639D86650F ON task_list');
        $this->addSql('ALTER TABLE task_list DROP user_id_id');
    }
}
