### Author
Remigijus Kiminas, Live Helper Chat

## Description
This plugin enables statistic generation using only Elastic Search. MySQL is not a good solution for statistic generation, but Elastic Search does great job on that.

## How it works
After chat close event chat is indexed within cronjob.
Also there is cronjob which indexes existing data.

## Future
Remove older chat's than 3 months and implement fallback if record is not found in MySQL. At the moment we just duplicate records.

## Install

1. Put elasticsearch folder in extensions folder.
2. copy settings.ini.default.php to settings.ini.php and edit settings
2. Activate extension in settings/settings.ini.php file
3. Go to back office and clear cache.
4. Go to Modules -> Elastic Search
5. Click create index and later update structure
6. Execute doc/install.sql

#Indexing chats
1. To index existing chats execute
php cron.php -s site_admin -e elasticsearch -c cron/index_chats

2. To index messages run
php cron.php -s site_admin -e elasticsearch -c cron/index_msg

2. To index online sessions run
php cron.php -s site_admin -e elasticsearch -c cron/index_os

Setup automatic indexing
1. Setup cronjob for every 5 minutes
*/5 * * * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/cron > log_index.txt /dev/null 2>&1

Setup indexing for pending, active chats and online oprators monitoring.
* * * * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/cron_1m > cron_1m.txt /dev/null 2>&1
