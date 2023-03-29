<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230329214103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE schedules (id INT AUTO_INCREMENT NOT NULL, fk_classroom_id_id INT NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, INDEX IDX_313BDC8ECA1855EB (fk_classroom_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE schedules ADD CONSTRAINT FK_313BDC8ECA1855EB FOREIGN KEY (fk_classroom_id_id) REFERENCES classrooms (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedules DROP FOREIGN KEY FK_313BDC8ECA1855EB');
        $this->addSql('DROP TABLE schedules');
    }
}
