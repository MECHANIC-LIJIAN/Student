#!/bin/bash
PIDS=`ps -ef|grep 'index.php datasToMysql'|grep -v grep|awk '{print $2}'`

if [ "$PIDS" != "" ]; then
echo "the process is runing at $PIDS!"
else
./redis_to_mysql.sh
PID=`ps -ef|grep 'index.php datasToMysql'|grep -v grep|awk '{print $2}'`
echo "the process has success runing at $PID!"
#运行进程
fi
