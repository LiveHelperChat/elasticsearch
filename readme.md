### Author
Remigijus Kiminas, Live Helper Chat

## Description
This plugin enables statistic generation using only Elastic Search. MySQL is not a good solution for statistic generation, but Elastic Search does great job on that.
 - Eliminates MySQL Queries in statistic genreation
 - Allows to search withing messages with keyword
 - Allows to generate Online Operators/Active/Pending chat's chart

## How it works?
 - After chat close event chat is indexed within cronjob.
 - Also there is cronjob which indexes existing data.

## Future plans
 - Remove older chat's than 3 months and implement fallback if record is not found in MySQL. At the moment we just duplicate records.

## Install

1. Put elasticsearch folder in extensions folder.
2. copy settings.ini.default.php to settings.ini.php and edit settings
2. Activate extension in settings/settings.ini.php file
3. Go to back office and clear cache.
4. Go to Modules -> Elastic Search
5. Click create index and later update structure
6. Execute doc/install.sql

#### Indexing existing chats
`php cron.php -s site_admin -e elasticsearch -c cron/index_chats`

#### Indexign existing messages
`php cron.php -s site_admin -e elasticsearch -c cron/index_msg`

#### Indexing existing online sessions
`php cron.php -s site_admin -e elasticsearch -c cron/index_os`

#### Setup automatic indexing
`*/5 * * * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/cron > log_index.txt /dev/null 2>&1`

#### Used to generate online operators/Active/Pending chat's chart
`* * * * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/cron_1m > cron_1m.txt /dev/null 2>&1`
