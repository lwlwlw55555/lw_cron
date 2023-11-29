#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 

#cp
scp /Users/lw/Code/leqee/bi/bi/bi-servlet/target/bi-servlet-0.0.1-SNAPSHOT.jar root@do.com:/opt/bi.jar


curl http://121.40.113.153/dp_props.php?restart=true

# /Library/Java/JavaVirtualMachines/jdk1.8.0_281.jdk/Contents/Home/bin/java -Dmaven.multiModuleProjectDirectory=/Users/lw/Code/leqee/bi/bi -Dmaven.home=/opt/homebrew/Cellar/maven/3.8.2/libexec -Dclassworlds.conf=/opt/homebrew/Cellar/maven/3.8.2/libexec/bin/m2.conf -Dfile.encoding=UTF-8 -classpath /opt/homebrew/Cellar/maven/3.8.2/libexec/boot/plexus-classworlds.license:/opt/homebrew/Cellar/maven/3.8.2/libexec/boot/plexus-classworlds-2.6.0.jar org.codehaus.classworlds.Launcher -s /opt/homebrew/Cellar/maven/3.8.2/libexec/conf/settings.xml -Dmaven.repo.local=/Users/lw/Code/factory -DskipTests=true clean

