<?php

namespace app\index\controller;

use think\Controller;

class Template extends Controller
{

    public function readTemplate()
    {

        $id = input('id');
        $template = model("Templates")->with('options')->where(['tid' => $id])->field('tid,tname,primaryKey,status')->find();
        if (!$template || $template['status'] != 1) {
            return $this->fetch('template', ['hello' => '该表单已关闭或未创建']);
        }
        
        $template = $template->toArray();
        $optionList = getOptionList($template['options'], $pid = 'pid', $id = 'sid');
        $templateField = [];
        foreach ($template['options'] as $value) {
            if ($value['pid'] == "0") {
                array_push($templateField, $value['sid']);
            }
        }
        cookie('options', $templateField);

        unset($template['options']);
        cookie('template', $template);
        cookie('ifCheck', 0);
        return $this->fetch('template', ['optionList' => $optionList, 'tname' => $template['tname']]);
    }

    public function collect()
    {
        if (request()->isAjax()) {
            $templateField = cookie('options');
            $template = cookie('template');
            $data['tid'] = $template['tid'];
            foreach ($templateField as $key => $value) {
                $data[$value] = input("post.$value");
            }

            if (cookie('ifCheck') == 0) {
                $res = model('TemplatesData')->where(['tid' => $template['tid'], $template['primaryKey'] => $data[$template['primaryKey']]])->find();

                if ($res) {
                    cookie('ifCheck', 1);
                    cookie('dataid', $res['id']);
                    return json([
                        'code' => 101,
                        'msg' => "该记录已存在，确认覆盖吗?",
                    ]);
                } else {
                    $res2 = model('TemplatesData')->allowField(true)->save($data);
                    model("Templates")->where('tid', $template['tid'])->setInc('count');
                }

                if ($res2) {
                    cookie('ifCheck', null);
                    $this->success('提交成功！', url('index/index/index'));
                } else {
                    $this->error('提交失败！');
                }
            } else {
                $res = model('TemplatesData')->allowField(true)->save($data, ['id' => cookie('dataid')]);
                if ($res) {
                    cookie('ifCheck', null);
                    $this->success('数据更新成功！', url('index/index/index'));
                } else {
                    $this->error('数据更新失败！');
                }
            }
        }
    }
}
