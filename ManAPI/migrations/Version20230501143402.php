<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230501143402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classrooms ADD fk_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE classrooms ADD CONSTRAINT FK_95F95DC25741EEB9 FOREIGN KEY (fk_user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_95F95DC25741EEB9 ON classrooms (fk_user_id)');
        $this->addSql('ALTER TABLE schedules ADD fk_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE schedules ADD CONSTRAINT FK_313BDC8E5741EEB9 FOREIGN KEY (fk_user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_313BDC8E5741EEB9 ON schedules (fk_user_id)');
        $this->addSql('ALTER TABLE students ADD fk_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB25741EEB9 FOREIGN KEY (fk_user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_A4698DB25741EEB9 ON students (fk_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classrooms DROP FOREIGN KEY FK_95F95DC25741EEB9');
        $this->addSql('DROP INDEX IDX_95F95DC25741EEB9 ON classrooms');
        $this->addSql('ALTER TABLE classrooms DROP fk_user_id');
        $this->addSql('ALTER TABLE schedules DROP FOREIGN KEY FK_313BDC8E5741EEB9');
        $this->addSql('DROP INDEX IDX_313BDC8E5741EEB9 ON schedules');
        $this->addSql('ALTER TABLE schedules DROP fk_user_id');
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_A4698DB25741EEB9');
        $this->addSql('DROP INDEX IDX_A4698DB25741EEB9 ON students');
        $this->addSql('ALTER TABLE students DROP fk_user_id');
    }
}
