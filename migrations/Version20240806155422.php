<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240806155422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task ADD task_list_id_id INT NOT NULL, ADD user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25BAC441FB FOREIGN KEY (task_list_id_id) REFERENCES task_list (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB259D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_527EDB25BAC441FB ON task (task_list_id_id)');
        $this->addSql('CREATE INDEX IDX_527EDB259D86650F ON task (user_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25BAC441FB');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB259D86650F');
        $this->addSql('DROP INDEX IDX_527EDB25BAC441FB ON task');
        $this->addSql('DROP INDEX IDX_527EDB259D86650F ON task');
        $this->addSql('ALTER TABLE task DROP task_list_id_id, DROP user_id_id');
    }
}
