<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251030 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS rule
            (
                id           binary(16)   not null primary key,
                name         varchar(255) null,
                uri          varchar(255) null,
                callback_url text null,
                method       varchar(10)  NOT NULL default \'post\',
                variables    json         null,
                headers      json         null,
                content_type varchar(255) null,
                query        text         null,
                constraint rules_unique_uri unique (uri)
            );
        ');

        $this->addSql('
            CREATE TABLE `query` (
              `id` binary(16) NOT NULL,
              `rule_id` binary(16) NOT NULL,
              `provider` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `request_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
              `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
              `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `content_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
              `error` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `attempts` int unsigned DEFAULT 0,
              `execute_time` varchar(30) DEFAULT NULL,
              `received_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_provider` (`provider`),
              KEY `idx_status` (`status`),
              KEY `idx_received_at` (`received_at`),
              KEY `query_rule_id_fk` (`rule_id`),
              CONSTRAINT `query_rule_id_fk` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
    }
}
