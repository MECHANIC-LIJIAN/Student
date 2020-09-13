#!/bin/bash
PIDS=`ps -ef|grep 'index.php datasToMysql'|grep -v grep|awk '{print $2}'`

if [ "$PIDS" != "" ]; then
echo "the insert process is runing at $PIDS!"
else
./redis_to_mysql.sh
PID=`ps -ef|grep 'index.php datasToMysql'|grep -v grep|awk '{print $2}'`
echo "the insert process has success runing at $PID!"
#运行进程
fi

PIDS=`ps -ef|grep 'index.php updateDatasToMysql'|grep -v grep|awk '{print $2}'`

if [ "$PIDS" != "" ]; then
echo "the update process is runing at $PIDS!"
else
./update_redis_to_mysql.sh
PID=`ps -ef|grep 'index.php updateDatasToMysql'|grep -v grep|awk '{print $2}'`
echo "the update process has success runing at $PID!"
#运行进程
fi
