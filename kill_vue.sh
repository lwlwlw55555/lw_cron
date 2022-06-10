#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 
echo 'begin kill 8081'

thread=$(lsof -i:8081 | awk '{print $2}' | grep -v PID)
echo $thread
if [ !$thread ]
then
    kill -9 $thread
else
    echo 'no 8081 thread'
fi
