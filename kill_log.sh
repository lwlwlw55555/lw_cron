#!/bin/sh
current=`date "+%Y-%m-%d.%H"` 
echo $current 
echo 'begin'

one_hour=`date -d "1 hour ago" "+%Y-%m-%d.%H"` 
echo $one_hour 

cd /mnt/log/jst
log=$(find jst* | grep -v $current |  grep -v $one_hour)
echo $log
#这是字符串的比较!!!!!整型的才能用-ne!!!!!
if [ "$log" = "" ]
then
	echo 'nomal'
else
	find jst* | grep -v $current |  grep -v $one_hour | xargs rm -r
fi

cd /mnt/log/dderpsync
log=$(find dderpsync* | grep -v $current)
echo $log
if [ "$log" = "" ]
then
	echo 'nomal'
else
	find dderpsync* | grep -v $current | xargs rm -r
fi


cd /mnt/log/dderpsyncinner
log=$(find dderpsync* | grep -v $current)
echo $log
if [ "$log" = "" ]
then
	echo 'nomal'
else
	find dderpsync* | grep -v $current | xargs rm -r
fi


# 1 */1 * * * root bash /home/wliu/kill_log.sh >> /mnt/log/express_erpcron/kill_log.log 2>&1