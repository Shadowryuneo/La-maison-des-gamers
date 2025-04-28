<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250419024739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF21E969C5');
        $this->addSql('DROP INDEX IDX_24CC0DF21E969C5 ON panier');
        $this->addSql('ALTER TABLE panier CHANGE utilisateurs_id utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('CREATE INDEX IDX_24CC0DF2FB88E14F ON panier (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2FB88E14F');
        $this->addSql('DROP INDEX IDX_24CC0DF2FB88E14F ON panier');
        $this->addSql('ALTER TABLE panier CHANGE utilisateur_id utilisateurs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF21E969C5 FOREIGN KEY (utilisateurs_id) REFERENCES utilisateurs (id)');
        $this->addSql('CREATE INDEX IDX_24CC0DF21E969C5 ON panier (utilisateurs_id)');
    }
}
