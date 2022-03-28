#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 
echo 'begin'

#这里不能有空格
#error: num= $(ps -ef | grep tomcat | grep -v grep | grep -v kill_restart_tomcat | wc -l)
#error: num= `ps -ef | grep tomcat | grep -v grep | grep -v kill_restart_tomcat | wc -l`
#error: num= ps -ef | grep tomcat | grep -v grep | grep -v kill_restart_tomcat | wc -l
#以上取出来的都是空！！！！下面判断肯定走不通啊！！！！
num=$(ps -ef | grep tomcat | grep -v grep | grep -v kill_restart_tomcat | wc -l)
echo $num
if [ $num -ne 3 ]
then
    sh /var/code/express_erpcron/kill_restart.sh
else
    echo 'nomal'
fi


#上面的一直在重启 不知道为什么 下面的靠谱一点？
# #!/bin/sh
# current=`date "+%Y-%m-%d %H:%M:%S"`  
# echo $current 
# echo 'begin'

# num=$(ps -ef | grep apache-tomcat | grep -v grep | grep -v kill_restart_tomcat | grep root | wc -l)
# echo $num
# log=$(ps -ef | grep apache-tomcat | grep -v grep | grep -v kill_restart_tomcat | grep root)
# echo $log
# if [ $num -ne 3 ]
# then
#     sh /var/code/express_erpcron/kill_restart.sh
# else
#     echo 'nomal'
# fi

#第二个还是在重启 不知道为什么 再改一次
# !/bin/sh
# current=`date "+%Y-%m-%d %H:%M:%S"`  
# echo $current 
# echo 'begin'

# num=$(ps -ef | grep apache-tomcat | grep -v grep | grep -v kill_restart_tomcat | grep root | wc -l)
# echo $num
# log=$(ps -ef | grep apache-tomcat | grep -v grep | grep -v kill_restart_tomcat | grep root)
# echo $log
# nu=$(echo $log | awk -F'root' '{print NF-1}')
# echo $nu
# if [ $nu -ne 3 ]
# then
#     sh /var/code/express_erpcron/kill_restart.sh
# else
#     echo 'nomal'
# fi

## kill_restart.sh代码如下
# #!/bin/sh
# current=`date "+%Y-%m-%d %H:%M:%S"`  
# echo $current 
# echo 'begin'

# ps -ef | grep tomcat | grep -v grep | grep -v kill_restart_tomcat | awk '{print $2}' | xargs kill -9

# cd /opt/apache-tomcat-8.0.33/bin/
# sh startup.sh
# cd /opt/apache-tomcat-8.0.33-8081/bin/
# sh startup.sh
# cd /opt/apache-tomcat-8.0.33-8082/bin/
# sh startup.sh
