#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 

cd /Users/lw/Code/leqee/bi/bi/



#clean
/Library/Java/JavaVirtualMachines/jdk1.8.0_281.jdk/Contents/Home/bin/java -Dmaven.multiModuleProjectDirectory=/Users/lw/Code/leqee/bi/bi -Dmaven.home=/opt/homebrew/Cellar/maven/3.8.2/libexec -Dclassworlds.conf=/opt/homebrew/Cellar/maven/3.8.2/libexec/bin/m2.conf -Dfile.encoding=UTF-8 -classpath /opt/homebrew/Cellar/maven/3.8.2/libexec/boot/plexus-classworlds.license:/opt/homebrew/Cellar/maven/3.8.2/libexec/boot/plexus-classworlds-2.6.0.jar org.codehaus.classworlds.Launcher -s /opt/homebrew/Cellar/maven/3.8.2/libexec/conf/settings.xml -Dmaven.repo.local=/Users/lw/Code/factory -DskipTests=true clean

#package
/Library/Java/JavaVirtualMachines/jdk1.8.0_281.jdk/Contents/Home/bin/java -Dmaven.multiModuleProjectDirectory=/Users/lw/Code/leqee/bi/bi -Dmaven.home=/opt/homebrew/Cellar/maven/3.8.2/libexec -Dclassworlds.conf=/opt/homebrew/Cellar/maven/3.8.2/libexec/bin/m2.conf -Dfile.encoding=UTF-8 -classpath /opt/homebrew/Cellar/maven/3.8.2/libexec/boot/plexus-classworlds.license:/opt/homebrew/Cellar/maven/3.8.2/libexec/boot/plexus-classworlds-2.6.0.jar org.codehaus.classworlds.Launcher -s /opt/homebrew/Cellar/maven/3.8.2/libexec/conf/settings.xml -Dmaven.repo.local=/Users/lw/Code/factory -DskipTests=true package

#cp
cp /Users/lw/Code/leqee/bi/bi/bi-servlet/target/bi-servlet-0.0.1-SNAPSHOT.jar ~/Downloads/bi.jar

#kill pre
echo 'begin kill 8085'

thread=$(lsof -i:8085 | awk '{print $2}' | grep -v PID)
echo $thread
if [ $thread ]
then
    kill -9 $thread
else
    echo 'no 8085 thread'
fi

cd ~/Downloads/

nohup java -jar bi.jar > bi.log &

cd /Users/lw/Code/leqee/bi/bi/

#clean
/Library/Java/JavaVirtualMachines/jdk1.8.0_281.jdk/Contents/Home/bin/java -Dmaven.multiModuleProjectDirectory=/Users/lw/Code/leqee/bi/bi -Dmaven.home=/opt/homebrew/Cellar/maven/3.8.2/libexec -Dclassworlds.conf=/opt/homebrew/Cellar/maven/3.8.2/libexec/bin/m2.conf -Dfile.encoding=UTF-8 -classpath /opt/homebrew/Cellar/maven/3.8.2/libexec/boot/plexus-classworlds.license:/opt/homebrew/Cellar/maven/3.8.2/libexec/boot/plexus-classworlds-2.6.0.jar org.codehaus.classworlds.Launcher -s /opt/homebrew/Cellar/maven/3.8.2/libexec/conf/settings.xml -Dmaven.repo.local=/Users/lw/Code/factory -DskipTests=true clean

#clean generater 上面的删不掉 不知道为什么...
# rm -rf /Users/lw/Code/leqee/bi/bi/target