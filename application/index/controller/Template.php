<?php

namespace app\index\controller;

use myredis\Redis;
use think\Controller;
use think\Db;

class Template extends Controller
{
    private $tid = "";
    private $template = [];
    private $redisKey = "";
    private $redis;
    private $datas;
    private $params;

    public function initialize()
    {
        $this->tid = input('id');

        if (request()->isAjax()) {
            $this->tid = input('post.tid');
        }

        $this->redisKey = 'template_' . $this->tid;

        $this->redis = new Redis();

        if (!$this->redis->exists($this->redisKey)) {
            //缓存不存在
            //查询数据
            $this->template = model("Templates")
                ->where(['tid' => $this->tid])
                ->field('id,tid,tname,remarks,primaryKey,status,myData,endTime,options')
                ->find();

            #不存在该id则置空
            if (!$this->template) {
                $this->redis->set($this->redisKey, null);
            } else {
                $this->template['options'] = json_decode($this->template['options'], true);
                $this->template['fields'] = array_keys($this->template['options']);

                #判断是否有主键
                if (!empty($this->template['primaryKey'])) {
                    $this->template['primaryKey'] = [
                        'field' => $this->template['primaryKey'],
                        'title' => $this->template['options'][$this->template['primaryKey']]['title'],
                    ];
                }
            }

            //转换成字符串，有利于存储
            $redisInfo = serialize($this->template);
            //存入缓存
            $this->redis->set($this->redisKey, $redisInfo);
            //设置缓存周期，60*30秒
            $this->redis->expire($this->redisKey, 60 * 30);
        }
   
        //获取缓存
        $this->template = unserialize($this->redis->get($this->redisKey));
        
        #判断表单是否存在
        if (empty($this->template)) {
            $this->error('该表单不存在');
        }

        #判断表单状态
        if ($this->template['status'] == "0") {
            $this->error('该表单已关闭');
        }

        #判断截止时间
        if ($this->template['endTime'] > 0 && $this->template['endTime'] < time()) {

            Db::name('templates')->where(['id' => $this->template['id']])->update(['status' => 0]);

            $this->redis->del($this->redisKey);
            $this->error('该表单已经停止提交');
        }

        if (request()->isAjax()) {
            $data['tid'] = $this->template['id'];
            #接受页面参数
            foreach ($this->template['fields'] as $value) {
                $input=input("post.$value");
                // if(empty(trim($input))){
                //     $this->error("输入内容不能为空");
                // }
                // if (!sensitive($input)) {
                //     $this->error('"'.$input.'"包含敏感词');
                // }
                $params[$value] = $input;
            }

            $data['content'] = json_encode($params);
            $data['update_time'] = time();
            $this->data=$data;
            $this->params=$params;
        }
    }

    /**
     * 显示表单页
     */

    public function readTemplate()
    {
        $optionList = $this->template['options'];

        unset($this->template['options']);

        return $this->fetch('template', [
            'optionList' => $optionList,
            'template' => $this->template,
            'remarks' => $this->template['remarks'],
        ]);
    }

    /**
     * 收集数据
     *
     * @return void
     */
    public function collect()
    {
        if (request()->isAjax()) {
            // $data['tid'] = $this->template['id'];
            // #接受页面参数
            // foreach ($this->template['fields'] as $value) {
            //     $params[$value] = input("post.$value");
            // }

            // $data['content'] = json_encode($params);
            // $data['create_time'] = time();
            // $data['update_time'] = time();
            $this->data['create_time']=time();
            #判断是否有主键
            if (!empty($this->template['primaryKey'])) {
                #找出提交数据的唯一字段的值
                $keyContent = $this->params[$this->template['primaryKey']['field']];

                #判断数据是否存在
                $res = model('Templates')->ifExist($this->template, $keyContent);
                if ($res) {
                    #数据存在，返回确认信息
                    return json([
                        'code' => 101,
                        'msg' => "该记录已存在，确认覆盖吗?",
                        'data' =>$res
                    ]);
                }

                #判断是否有参考数据集并且数据是否在其中
                if (!empty($this->template['myData'])) {
                    $res = model('Templates')->ifUseData($this->template, $keyContent);
                    if ($res != 1) {
                        return $this->error($res);
                    }
                }
            }

            #保存新数据
            $res = model('Templates')->postData($this->data);
            if ($res == 1) {
                $this->success('提交成功！', url('index/index/index',['msg'=>"感谢您在《".$this->template['tname']."》的提交。"]));
            } else {
                $this->error($res);
            }
        }
    }

    public function collectUpdate()
    {
        // $data['tid'] = $this->template['id'];
        // #接受页面参数
        // foreach ($this->template['fields'] as $value) {
        //     $params[$value] = input("post.$value");
        // }

        // $data['content'] = json_encode($params);
        // $data['update_time'] = time();
        #是覆盖确认，更新数据
        
        $this->data['id']=input('recordid');
        $this->data['isUpdate']=1;
        $res = model('Templates')->updatePostData($this->data);
        if ($res) {
            $this->success('数据更新成功！', url('index/index/index',['msg'=>"感谢您在《".$this->template['tname']."》的提交。"]));
        } else {
            $this->error('数据更新失败！');
        }
    }

}
