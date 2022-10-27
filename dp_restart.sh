#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 


service dp restart &


# /usr/java/jdk1.8.0_311/bin/java -Xmx1024m -Xms512m -Xss256k -jar /opt/bi.jar 
# ExecStop=/usr/bin/kill -15  $MAINPID

# /usr/java/jdk1.8.0_311/bin/java -Xmx512m -Xms256m -Xss256k -jar /opt/bi.jar 

# sudo su

# #kill pre
# echo 'begin kill 8199'

# # lsof -i:8199 | awk '{print $2}' | grep -v PID xargs kill -9

# thread=$(lsof -i:8199 | awk '{print $2}' | grep -v PID | wc -l)
# echo $thread
# #这里中括号[]前后都要有空格啊啊啊啊啊！不然会报错的！！
# if [ $thread -gt 0 ]
# then
#     lsof -i:8199 | awk '{print $2}' | grep -v PID | xargs kill -9
# else
#     echo 'no 8199 thread'
# fi


# nohup /usr/java/jdk1.8.0_311/bin/java -Xmx512m -Xms256m -Xss256k -jar /opt/bi.jar &
# # nohup /usr/java/jdk1.8.0_311/bin/java -Xmx512m -Xms256m -Xss256k -jar /opt/do_com_admin.jar &


# "\"2022-10-27 11:47:23\\n 11:47:23 up 260 days, 21:45,  3 users,  load average: 1.24, 1.42, 1.46\\nUSER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT\\nroot     pts\\/0    101.68.82.114    09:36    1:54m  0.10s  0.07s -bash\\nroot     pts\\/1    101.68.82.114    10:29   10.00s  0.62s  0.01s bash\\nroot     pts\\/2    101.68.82.114    11:20    5:53   0.13s  0.13s -bash\\n\""