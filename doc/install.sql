CREATE TABLE `lhc_lheschat_index` (
  `chat_id` bigint(20) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `chat_id` (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lhc_lhesmail_index` (
                                      `mail_id` bigint(20) unsigned NOT NULL,
                                      `status` tinyint(1) unsigned NOT NULL DEFAULT 0,
                                      `op` tinyint(1) unsigned NOT NULL DEFAULT 0,
                                      `udate` bigint(20) unsigned NOT NULL DEFAULT 0,
                                      UNIQUE KEY `mail_id_op` (`mail_id`,`op`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lhc_mailconv_delete_filter_elastic` (
                                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                              `updated_at` bigint(20) unsigned NOT NULL,
                                              `created_at` bigint(20) unsigned NOT NULL,
                                              `user_id` bigint(20) unsigned NOT NULL,
                                              `archive_id` int(11) unsigned NOT NULL DEFAULT 0,
                                              `status` tinyint(1) unsigned NOT NULL DEFAULT 0,
                                              `last_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                              `started_at` bigint(20) unsigned NOT NULL DEFAULT 0,
                                              `finished_at` bigint(20) unsigned NOT NULL DEFAULT 0,
                                              `processed_records` bigint(20) unsigned NOT NULL DEFAULT 0,
                                              `filter` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                                              `filter_input` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lhc_mailconv_delete_item_elastic` (
                                                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                    `conversation_id` bigint(20) unsigned NOT NULL,
                                                    `filter_id` bigint(20) unsigned NOT NULL,
                                                    `status` tinyint(1) unsigned NOT NULL DEFAULT 0,
                                                    `index` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                    PRIMARY KEY (`id`),
                                                    KEY `filter_id_status` (`filter_id`,`status`),
                                                    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
