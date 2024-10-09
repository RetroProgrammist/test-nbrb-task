#!/usr/bin/env bash

#write out current crontab
crontab -l > mycron
#echo new cron into cron file
echo "*/1 * * * * /usr/bin/php /var/www/html/index.php cron" >> mycron
#install new cron file
crontab mycron
rm mycron