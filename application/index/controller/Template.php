<?php

namespace app\index\controller;

use myredis\Redis;
use think\Controller;
use think\Db;
use think\facade\Log;

class Template extends Controller
{

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

    public function datasToMysql()
    {
        $redis = new Redis();
        $redisKey = 'datalists';
        while (true) {
            $datas = $redis->lRange($redisKey, 0, -1);
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
            sleep(3);
        }
    }

    public function readTemplate()
    {
        $id = input('id');

        $redisKey = 'template_' . $id;
        $redis = new Redis();
        //判断是否过期
        if ($redis->exists($redisKey) == 0) {
            //缓存失效，重新存入
            //查询数据
            $template = model("Templates")
                ->where(['tid' => $id])
                ->field('id,tid,tname,remarks,primaryKey,status,myData,options')
                ->find();
            if ($template) {

                $template['options'] = json_decode($template['options'], true);
                $template['fields'] = array_keys($template['options']);

                #判断是否有主键
                if (!empty($template['primaryKey'])) {
                    $template['primaryKey'] = [
                        'field' => $template['primaryKey'],
                        'title' => $template['options'][$template['primaryKey']]['title'],
                    ];
                }

                //转换成字符串，有利于存储
                $redisInfo = serialize($template);
                //存入缓存
                $redis->set($redisKey, $redisInfo);
                //设置缓存周期，60*30秒
                $redis->expire($redisKey, 60 * 30);
            }
        }
        //获取缓存
        $template = unserialize($redis->get($redisKey));

        if (!$template || $template['status'] != 1) {
            return $this->fetch('template', ['info' => '该表单已关闭或未创建']);
        }

        $optionList = $template['options'];
        unset($template['options']);
        // dump($template);
        // dump($optionList);
        cookie('template', $template);
        cookie('ifCheck', 0);
        cookie('content', "");

        return $this->fetch('template', ['optionList' => $optionList, 'tname' => $template['tname'],'remarks' => $template['remarks']]);
    }

    public function collect()
    {

        if (request()->isAjax()) {

            $template = json_decode(cookie('template'), true);
            $templateField = $template['fields'];

            $data['tid'] = $template['id'];
            #接受页面参数
            foreach ($templateField as $value) {
                $params[$value] = input("post.$value");
            }

            $data['content'] = json_encode($params);
            $data['create_time'] = time();
            $data['update_time'] = time();

            #判断是否有主键
            if (!empty($template['primaryKey'])) {
                #找出提交数据的唯一字段的值
                $keyContent = $params[$template['primaryKey']['field']];
                #判断是否有参考数据集并且数据是否在其中
                if (!empty($template['myData'])) {
                    $res = model('Templates')->ifUseData($template, $keyContent);
                    if ($res != 1) {
                        return $this->error($res);
                    }
                }
                #判断是否为覆盖确认
                if (cookie('ifCheck') == 1) {
                    #是覆盖确认，更新数据
                    $res = model('TemplatesDatas')->allowField(true)->save($data, ['id' => cookie('dataid')]);
                    if ($res) {
                        cookie('ifCheck', null);
                        cookie('content', '感谢您在' . $template['tname'] . '的提交');
                        $this->success('数据更新成功！', url('index/index/index'));
                    } else {
                        $this->error('数据更新失败！');
                    }
                }
                #判断数据是否存在
                $res = model('Templates')->ifExist($template, $keyContent);
                if ($res == 1) {
                    #数据存在，返回确认信息
                    return json([
                        'code' => 101,
                        'msg' => "该记录已存在，确认覆盖吗?",
                    ]);
                }
            }

            #该页面第一次提交
            #保存新数据
            $res = model('Templates')->saveData($template, $data);
            if ($res == 1) {
                cookie('ifCheck', null);
                cookie('content', '感谢您在' . $template['tname'] . '的提交');
                $this->success('提交成功！', url('index/index/index'));
            } else {
                $this->error($res);
            }
        }
    }
}
