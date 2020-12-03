### Author
Remigijus Kiminas, Live Helper Chat

### ElasticSearch versions support
 - For ElasticSearch 7 use master branch
 - For ElasticSearch 5-6 use master-5.x-6.x branch

## Description
This plugin enables statistic generation using only Elastic Search. MySQL is not a good solution for statistic generation, but Elastic Search does great job on that.
 - Eliminates MySQL Queries in statistic generation
 - Allows to search withing messages with keyword
 - Allows to generate Online Operators/Active/Pending chat's chart
 
![See image](https://livehelperchat.com/design/frontendnew/images/stats.png)

## How it works?
 - After chat close event chat is indexed within cronjob.
 - Also there is cronjob which indexes existing data.

## Future plans
 - Remove older chat's than 3 months and implement fallback if record is not found in MySQL. At the moment we just duplicate records.

## Install

1. Put elasticsearch folder in extensions folder.
2. copy `extension/elasticsearch/settings.ini.default.php` to `extension/elasticsearch/settings.ini.php` and edit settings
3. Activate extension in main settings `lhc_web/settings/settings.ini.php` file
```
'extensions' => 
      array (
        'elasticsearch'
      ),
```
3. Install dependencies `composer install` from extension folder
3. Go to back office and clear cache.
4. Go to Modules -> Elastic Search
5. Click create index and later update structure
6. Execute doc/install.sql

#### Indexing existing chats
`php cron.php -s site_admin -e elasticsearch -c cron/index_chats`

#### Indexing existing messages
`php cron.php -s site_admin -e elasticsearch -c cron/index_msg`

#### Indexing existing online sessions
`php cron.php -s site_admin -e elasticsearch -c cron/index_os`

#### Setup automatic indexing [Required]
`*/5 * * * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/cron > log_index.txt /dev/null 2>&1`

#### Used to generate online operators/Active/Pending chat's chart [Optional]
`* * * * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/cron_1m > cron_1m.txt /dev/null 2>&1`

#### Using daily/monthly index

I recommend if you are planning to have thousands of chats per day to use monthly index.

If you are running daily/monthly index you should be running this cronjob daily.

`5 12 * * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/index_precreate`

#### Monitoring Elasticsearch

To receive notification about failure of Elasticsearch you can run this cronjob. It will automatically turn on/off Elasticsearch extension.

`* * * * * cd /home/www/lhc && php cron.php -s site_admin -e elasticsearch -c cron/check_health`

