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