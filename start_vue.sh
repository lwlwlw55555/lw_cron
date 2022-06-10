#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 
echo 'begin'

thread=$(lsof -i:8081 | awk '{print $2}' | grep -v PID)
echo $thread
if [ $thread -ne '' ]
then
    kill -9 $thread
else
    echo 'no 8081 thread'
fi

cd /var/code/html/vueblog-vue

/usr/bin/npm install --save github-markdown-css

nohup /usr/bin/npm run serve > /mnt/lw_vue &