<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230329204523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE establishments (id INT AUTO_INCREMENT NOT NULL, fk_user_id_id INT NOT NULL, name VARCHAR(63) NOT NULL, INDEX IDX_5C67EFC56DE8AF9C (fk_user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE establishments ADD CONSTRAINT FK_5C67EFC56DE8AF9C FOREIGN KEY (fk_user_id_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE establishments DROP FOREIGN KEY FK_5C67EFC56DE8AF9C');
        $this->addSql('DROP TABLE establishments');
    }
}
