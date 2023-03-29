<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230329205923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE classrooms (id INT AUTO_INCREMENT NOT NULL, fk_establishment_id_id INT NOT NULL, name VARCHAR(127) NOT NULL, INDEX IDX_95F95DC22D30A987 (fk_establishment_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classrooms ADD CONSTRAINT FK_95F95DC22D30A987 FOREIGN KEY (fk_establishment_id_id) REFERENCES establishments (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classrooms DROP FOREIGN KEY FK_95F95DC22D30A987');
        $this->addSql('DROP TABLE classrooms');
    }
}
