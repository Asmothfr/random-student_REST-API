<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410170948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classrooms DROP FOREIGN KEY FK_95F95DC22D30A987');
        $this->addSql('DROP INDEX IDX_95F95DC22D30A987 ON classrooms');
        $this->addSql('ALTER TABLE classrooms CHANGE fk_establishment_id_id fk_establishment_id INT NOT NULL');
        $this->addSql('ALTER TABLE classrooms ADD CONSTRAINT FK_95F95DC25E3C3F26 FOREIGN KEY (fk_establishment_id) REFERENCES establishments (id)');
        $this->addSql('CREATE INDEX IDX_95F95DC25E3C3F26 ON classrooms (fk_establishment_id)');
        $this->addSql('ALTER TABLE establishments DROP FOREIGN KEY FK_5C67EFC56DE8AF9C');
        $this->addSql('DROP INDEX IDX_5C67EFC56DE8AF9C ON establishments');
        $this->addSql('ALTER TABLE establishments CHANGE fk_user_id_id fk_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE establishments ADD CONSTRAINT FK_5C67EFC55741EEB9 FOREIGN KEY (fk_user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_5C67EFC55741EEB9 ON establishments (fk_user_id)');
        $this->addSql('ALTER TABLE schedules DROP FOREIGN KEY FK_313BDC8ECA1855EB');
        $this->addSql('DROP INDEX IDX_313BDC8ECA1855EB ON schedules');
        $this->addSql('ALTER TABLE schedules CHANGE fk_classroom_id_id fk_classroom_id INT NOT NULL');
        $this->addSql('ALTER TABLE schedules ADD CONSTRAINT FK_313BDC8E43A5D7B1 FOREIGN KEY (fk_classroom_id) REFERENCES classrooms (id)');
        $this->addSql('CREATE INDEX IDX_313BDC8E43A5D7B1 ON schedules (fk_classroom_id)');
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_A4698DB2CA1855EB');
        $this->addSql('DROP INDEX IDX_A4698DB2CA1855EB ON students');
        $this->addSql('ALTER TABLE students CHANGE fk_classroom_id_id fk_classroom_id INT NOT NULL');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB243A5D7B1 FOREIGN KEY (fk_classroom_id) REFERENCES classrooms (id)');
        $this->addSql('CREATE INDEX IDX_A4698DB243A5D7B1 ON students (fk_classroom_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classrooms DROP FOREIGN KEY FK_95F95DC25E3C3F26');
        $this->addSql('DROP INDEX IDX_95F95DC25E3C3F26 ON classrooms');
        $this->addSql('ALTER TABLE classrooms CHANGE fk_establishment_id fk_establishment_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE classrooms ADD CONSTRAINT FK_95F95DC22D30A987 FOREIGN KEY (fk_establishment_id_id) REFERENCES establishments (id)');
        $this->addSql('CREATE INDEX IDX_95F95DC22D30A987 ON classrooms (fk_establishment_id_id)');
        $this->addSql('ALTER TABLE establishments DROP FOREIGN KEY FK_5C67EFC55741EEB9');
        $this->addSql('DROP INDEX IDX_5C67EFC55741EEB9 ON establishments');
        $this->addSql('ALTER TABLE establishments CHANGE fk_user_id fk_user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE establishments ADD CONSTRAINT FK_5C67EFC56DE8AF9C FOREIGN KEY (fk_user_id_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_5C67EFC56DE8AF9C ON establishments (fk_user_id_id)');
        $this->addSql('ALTER TABLE schedules DROP FOREIGN KEY FK_313BDC8E43A5D7B1');
        $this->addSql('DROP INDEX IDX_313BDC8E43A5D7B1 ON schedules');
        $this->addSql('ALTER TABLE schedules CHANGE fk_classroom_id fk_classroom_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE schedules ADD CONSTRAINT FK_313BDC8ECA1855EB FOREIGN KEY (fk_classroom_id_id) REFERENCES classrooms (id)');
        $this->addSql('CREATE INDEX IDX_313BDC8ECA1855EB ON schedules (fk_classroom_id_id)');
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_A4698DB243A5D7B1');
        $this->addSql('DROP INDEX IDX_A4698DB243A5D7B1 ON students');
        $this->addSql('ALTER TABLE students CHANGE fk_classroom_id fk_classroom_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB2CA1855EB FOREIGN KEY (fk_classroom_id_id) REFERENCES classrooms (id)');
        $this->addSql('CREATE INDEX IDX_A4698DB2CA1855EB ON students (fk_classroom_id_id)');
    }
}
