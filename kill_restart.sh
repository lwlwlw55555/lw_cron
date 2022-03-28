#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 
echo 'begin'

ps -ef | grep tomcat | grep -v grep | grep -v kill_restart_tomcat | awk '{print $2}' | xargs kill -9

cd /opt/apache-tomcat-8.0.33/bin/
sh startup.sh
cd /opt/apache-tomcat-8.0.33-8081/bin/
sh startup.sh
cd /opt/apache-tomcat-8.0.33-8082/bin/
sh startup.sh