<?php

namespace app\index\controller;

use think\Controller;

class Template extends Controller
{

    public function readTemplate()
    {

        $id = input('id');
        $template = model("Templates")
            ->where(['tid' => $id])
            ->field('tid,tname,primaryKey,status,ifUseData,myData,options')
            ->find()
            ->toArray();

        if (!$template || $template['status'] != 1) {
            return $this->fetch('template', ['hello' => '该表单已关闭或未创建']);
        }
        
        $template['options']=json_decode($template['options'],true);

        $template['fields']=array_keys($template['options']);
        // dump($template);
        cookie('template', $template);
        cookie('ifCheck', 0);
        return $this->fetch('template', ['optionList' => $template['options'], 'tname' => $template['tname']]);
    }

    public function collect()
    {
        if (request()->isAjax()) {

            $template = cookie('template');
            $templateField=$template['fields'];
            

            #接受页面参数
            foreach ($templateField as $key => $value) {
                $params[$value] = input("post.$value");
            }

            #找出唯一字段的值
            $keyContent = $params[$template['primaryKey']];

            $data['content']=json_encode($params);
            $data['tid'] = $template['tid'];

            #判断是否有参考数据集
            if ($template['ifUseData'] == 1) {
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
                    $this->success('数据更新成功！', url('index/index/index'));
                } else {
                    $this->error('数据更新失败！');
                }
            }

            #该页面第一次提交
            #判断数据是否存在
            $res = model('Templates')->ifExist($template, $keyContent);
            if ($res == 1) {
                #数据存在，返回确认信息
                return json([
                    'code' => 101,
                    'msg' => "该记录已存在，确认覆盖吗?",
                ]);
            } else {
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
}
