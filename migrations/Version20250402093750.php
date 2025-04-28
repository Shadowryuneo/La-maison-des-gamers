<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402093750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adressedelivraison ADD utilisateurs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adressedelivraison ADD CONSTRAINT FK_F01790DC1E969C5 FOREIGN KEY (utilisateurs_id) REFERENCES utilisateurs (id)');
        $this->addSql('CREATE INDEX IDX_F01790DC1E969C5 ON adressedelivraison (utilisateurs_id)');
        $this->addSql('ALTER TABLE adressefacturation ADD utilisateurs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adressefacturation ADD CONSTRAINT FK_1BBDD1351E969C5 FOREIGN KEY (utilisateurs_id) REFERENCES utilisateurs (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BBDD1351E969C5 ON adressefacturation (utilisateurs_id)');
        $this->addSql('ALTER TABLE avis ADD utilisateurs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF01E969C5 FOREIGN KEY (utilisateurs_id) REFERENCES utilisateurs (id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF01E969C5 ON avis (utilisateurs_id)');
        $this->addSql('ALTER TABLE commande ADD utilisateurs_id INT DEFAULT NULL, ADD adresselivraisoncommande_id INT DEFAULT NULL, ADD adressefacturationcommande_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D1E969C5 FOREIGN KEY (utilisateurs_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DBE1786DF FOREIGN KEY (adresselivraisoncommande_id) REFERENCES adresselivraisoncommande (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DF3FE27CA FOREIGN KEY (adressefacturationcommande_id) REFERENCES adressefacturationcommande (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D1E969C5 ON commande (utilisateurs_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6EEAA67DBE1786DF ON commande (adresselivraisoncommande_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6EEAA67DF3FE27CA ON commande (adressefacturationcommande_id)');
        $this->addSql('ALTER TABLE detailcommande ADD produit_id INT DEFAULT NULL, ADD commande_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE detailcommande ADD CONSTRAINT FK_7D6DC7D5F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE detailcommande ADD CONSTRAINT FK_7D6DC7D582EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('CREATE INDEX IDX_7D6DC7D5F347EFB ON detailcommande (produit_id)');
        $this->addSql('CREATE INDEX IDX_7D6DC7D582EA2E54 ON detailcommande (commande_id)');
        $this->addSql('ALTER TABLE panier ADD produit_id INT DEFAULT NULL, ADD utilisateurs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF21E969C5 FOREIGN KEY (utilisateurs_id) REFERENCES utilisateurs (id)');
        $this->addSql('CREATE INDEX IDX_24CC0DF2F347EFB ON panier (produit_id)');
        $this->addSql('CREATE INDEX IDX_24CC0DF21E969C5 ON panier (utilisateurs_id)');
        $this->addSql('ALTER TABLE payement ADD commande_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payement ADD CONSTRAINT FK_B20A788582EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('CREATE INDEX IDX_B20A788582EA2E54 ON payement (commande_id)');
        $this->addSql('ALTER TABLE utilisateurs CHANGE password password VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adressedelivraison DROP FOREIGN KEY FK_F01790DC1E969C5');
        $this->addSql('DROP INDEX IDX_F01790DC1E969C5 ON adressedelivraison');
        $this->addSql('ALTER TABLE adressedelivraison DROP utilisateurs_id');
        $this->addSql('ALTER TABLE adressefacturation DROP FOREIGN KEY FK_1BBDD1351E969C5');
        $this->addSql('DROP INDEX UNIQ_1BBDD1351E969C5 ON adressefacturation');
        $this->addSql('ALTER TABLE adressefacturation DROP utilisateurs_id');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF01E969C5');
        $this->addSql('DROP INDEX IDX_8F91ABF01E969C5 ON avis');
        $this->addSql('ALTER TABLE avis DROP utilisateurs_id');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D1E969C5');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DBE1786DF');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DF3FE27CA');
        $this->addSql('DROP INDEX IDX_6EEAA67D1E969C5 ON commande');
        $this->addSql('DROP INDEX UNIQ_6EEAA67DBE1786DF ON commande');
        $this->addSql('DROP INDEX UNIQ_6EEAA67DF3FE27CA ON commande');
        $this->addSql('ALTER TABLE commande DROP utilisateurs_id, DROP adresselivraisoncommande_id, DROP adressefacturationcommande_id');
        $this->addSql('ALTER TABLE detailcommande DROP FOREIGN KEY FK_7D6DC7D5F347EFB');
        $this->addSql('ALTER TABLE detailcommande DROP FOREIGN KEY FK_7D6DC7D582EA2E54');
        $this->addSql('DROP INDEX IDX_7D6DC7D5F347EFB ON detailcommande');
        $this->addSql('DROP INDEX IDX_7D6DC7D582EA2E54 ON detailcommande');
        $this->addSql('ALTER TABLE detailcommande DROP produit_id, DROP commande_id');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2F347EFB');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF21E969C5');
        $this->addSql('DROP INDEX IDX_24CC0DF2F347EFB ON panier');
        $this->addSql('DROP INDEX IDX_24CC0DF21E969C5 ON panier');
        $this->addSql('ALTER TABLE panier DROP produit_id, DROP utilisateurs_id');
        $this->addSql('ALTER TABLE payement DROP FOREIGN KEY FK_B20A788582EA2E54');
        $this->addSql('DROP INDEX IDX_B20A788582EA2E54 ON payement');
        $this->addSql('ALTER TABLE payement DROP commande_id');
        $this->addSql('ALTER TABLE utilisateurs CHANGE password password VARCHAR(255) NOT NULL');
    }
}
