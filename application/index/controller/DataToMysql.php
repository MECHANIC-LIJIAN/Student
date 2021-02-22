<?php

namespace app\index\controller;

use myredis\Redis;
use think\Controller;
use think\Db;
use think\facade\Log;

class DataToMysql extends Controller
{
    public function datasToMysql()
    {
        $redis = new Redis();
        $redisKey = 'datalists';

        $mainQueueKey = 'datalists';
        $subQueueKey = 'subdatalist';

        while (true) {
            #加锁
            $redis->set("LOCK_insert", 1);
            $datas = $redis->lRange($redisKey, 0, -1);
            Db::name('TemplatesDatas')->field('id')->find(1);

            if (!empty($datas)) {
                foreach ($datas as &$value) {
                    $value = unserialize($value);
                }
                // 启动事务
                Db::startTrans();
                try {
                    Db::name('TemplatesDatas')->data($datas)->limit(100)->insertAll();
                    // 提交事务
                    Db::commit();
                    $redis->del($redisKey);
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    Log::write($e->getMessage(), 'error');
                }
            }
            #释放锁
            $redis->set("LOCK_insert", 0);

            #转移子队列数据
            $subDatas=$redis->lRange($subQueueKey, 0, -1);
            foreach ($subDatas as $value) {
                $redis->rPush($mainQueueKey, $value);
            }
            $redis->del($subQueueKey);

            sleep(1);
        }
    }

    public function updateDatasToMysql()
    {
        $redis = new Redis();
        $redisKey = 'updatedatalists';

        while (true) {
            $datas = $redis->lRange($redisKey, 0, -1);

            Db::name('TemplatesDatas')->field('id')->find(1);
            if (!empty($datas)) {
                foreach ($datas as &$value) {
                    $value = unserialize($value);
                }
                // 启动事务
                Db::startTrans();
                try {
                    model('TemplatesDatas')->saveAll($datas);
                    // 提交事务
                    Db::commit();
                    $redis->del($redisKey);
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    Log::write($e->getMessage(), 'error');
                }
            }
            sleep(2);
        }
    }

    public function getTestdatas()
    {
        $limit = input('limit', 1000);

        debug('test');
        $redis = new Redis();
        $redisKey = 'datalists';

        // $redis->del($redisKey);
        $testDatas = Db::name("TemplatesDatas")
            ->whereNull('delete_time')
            ->limit($limit)
            ->field('id', true)
            ->select();

        foreach ($testDatas as &$value) {
            $value['update_time'] = time();
            $value['create_time'] = time();
            $nLen = $redis->lPush($redisKey, serialize($value));
        }
        dump($nLen . "条压入redis");
        dump($redis->lLen($redisKey));
        echo debug('test', 'testend');
    }
    public function saveTestdatas()
    {
        dump("--------------------------------------");
        debug('begin');
        $redis = new Redis();
        $redisKey = 'datalists';
        $datas = $redis->lRange($redisKey, 0, -1);
        dump($redis->lLen($redisKey));
        foreach ($datas as &$value) {
            $value = unserialize($value);
            if (empty($value['tid'])) {
                dump($value);
            }
        }

        debug('end');
        dump(debug('begin', 'end') . 's');
        dump(debug('begin', 'end', 'm') . 'kb');

        dump("--------------------------------------");
        dump("db to mysql");
        debug('begin');

        #启动事务
        Db::startTrans();
        try {
            Db::name('TemplatesDatas')->limit(100)->insertAll($datas);
            // 提交事务
            Db::commit();
            $redis->del($redisKey);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            dump($e->getMessage());
            Log::write($e->getMessage(), 'error');
        }
        debug('end');
        dump(debug('begin', 'end') . 's');
        dump(debug('begin', 'end', 'm') . 'kb');
    }
}
