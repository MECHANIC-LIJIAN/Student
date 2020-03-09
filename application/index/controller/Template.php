<?php

namespace app\index\controller;

use think\Controller;
use think\cache\driver\Redis;
class Template extends Controller
{

    public function readTemplate()
    {

        $id = input('id');

        $redisKey = $id;
        $redis = new Redis();
        //判断是否过期
        $redis_status = $redis->exists($redisKey);
        if ($redis_status == false) {
            //缓存失效，重新存入
            //查询数据
            $template = model("Templates")
                ->where(['tid' => $id])
                ->field('tid,tname,primaryKey,status,myData,options')
                ->find()
                ->toArray();
            //转换成字符串，有利于存储
            $redisInfo = serialize($template);
            //存入缓存
            $redis->set($redisKey, $redisInfo);
            //设置缓存周期，60秒
            $redis->expire($redisKey, 60);
        }
        //获取缓存
        $template = unserialize($redis->get($redisKey));

        if (!$template || $template['status'] != 1) {
            return $this->fetch('template', ['info' => '该表单已关闭或未创建']);
        }

        $template['options'] = json_decode($template['options'], true);
        $template['fields'] = array_keys($template['options']);
        
        cookie('template', $template);
        cookie('ifCheck', 0);
        return $this->fetch('template', ['optionList' => $template['options'], 'tname' => $template['tname']]);
    }

    public function collect()
    {
        if (request()->isAjax()) {

            $template = cookie('template');
            $templateField = $template['fields'];

            #接受页面参数
            foreach ($templateField as $key => $value) {
                $params[$value] = input("post.$value");
            }

            $data['content'] = json_encode($params);
            $data['tid'] = $template['tid'];
            
            #判断是否有主键
            if (!empty($template['primaryKey'])) {
                #找出唯一字段的值
                $keyContent = $params[$template['primaryKey']];
                #判断是否有参考数据集并且数据是否在其中
                if (!empty($template['myData'])) {
                    $res = model('Templates')->ifUseData($template, $keyContent);
                    return $res;
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
                $this->success('提交成功！', url('index/index/index'));
            } else {
                $this->error($res);
            }
        }
    }
}
