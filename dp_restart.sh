#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 

#kill pre
echo 'begin kill 8199'

# lsof -i:8199 | awk '{print $2}' | grep -v PID xargs kill -9

thread=$(lsof -i:8199 | awk '{print $2}' | grep -v PID | wc -l)
echo $thread
#这里中括号[]前后都要有空格啊啊啊啊啊！不然会报错的！！
if [ $thread -gt 0 ]
then
    lsof -i:8199 | awk '{print $2}' | grep -v PID | xargs kill -9
else
    echo 'no 8199 thread'
fi


nohup /usr/java/jdk1.8.0_311/bin/java -Xmx512m -Xms256m -Xss256k -jar /opt/bi.jar &
# nohup /usr/java/jdk1.8.0_311/bin/java -Xmx512m -Xms256m -Xss256k -jar /opt/do_com_admin.jar &
