<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201010211020 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE negocio (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, icono VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE categoria CHANGE icono icono VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pedido CHANGE trabajador_id trabajador_id INT DEFAULT NULL, CHANGE pago_viaje pago_viaje DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE producto ADD negocio_id INT DEFAULT NULL, CHANGE img img VARCHAR(255) DEFAULT NULL, CHANGE cant_min cant_min INT DEFAULT NULL');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT FK_A7BB06157D879E4F FOREIGN KEY (negocio_id) REFERENCES negocio (id)');
        $this->addSql('CREATE INDEX IDX_A7BB06157D879E4F ON producto (negocio_id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE correo correo VARCHAR(255) DEFAULT NULL, CHANGE telf telf VARCHAR(255) DEFAULT NULL, CHANGE registro registro DATETIME DEFAULT NULL, CHANGE id_telegram id_telegram VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY FK_A7BB06157D879E4F');
        $this->addSql('DROP TABLE negocio');
        $this->addSql('ALTER TABLE categoria CHANGE icono icono VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE pedido CHANGE trabajador_id trabajador_id INT DEFAULT NULL, CHANGE pago_viaje pago_viaje DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('DROP INDEX IDX_A7BB06157D879E4F ON producto');
        $this->addSql('ALTER TABLE producto DROP negocio_id, CHANGE img img VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE cant_min cant_min INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE correo correo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE telf telf VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE registro registro DATETIME DEFAULT \'NULL\', CHANGE id_telegram id_telegram VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
