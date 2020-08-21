<?php

namespace app\admin\controller;

use Overtrue\Pinyin\Pinyin;

class Hand extends Base
{
    public function index()
    {
        return view();
    }

    public function word()
    {
        return view('word');
    }

    public function add()
    {
        if (request()->isAjax()) {

            $params = input('post.');
            $tInfo = [];

            #判断表单类型
            if ($params['option_1_rule'] == 'word|required') {

                if (!array_key_exists('option_2', $params)) {
                    $this->error("请至少添加一个字段");
                }

                $wordfile = request()->file('wordfile');

                if ($wordfile == null) {
                    $this->error("请上传模板文件");
                }
                // 移动到框架应用根目录/uploads/ 目录下
                $info = $wordfile->move('uploads/word/wordfile');
                if ($info) {
                    // 成功上传后 获取上传信息
                    $wordPath =  "uploads/word/wordfile/" .$info->getSaveName();
                } else {
                    // 上传失败获取错误信息
                    $this->error("文件上传失败");
                }

                $tInfo['word_path'] = $wordPath;
                $tInfo['ttype'] = 1;
            }

            // halt($tInfo);
            if ((!array_key_exists('option_1', $params))) {
                $this->error("请至少添加一个字段");
            }

            $pinyin = new Pinyin();
            $tInfo = $tInfo + [
                'tid' => uuid(),
                'uid' => session('admin.id'),
                'tname' => $params['templateName'],
                'remarks' => $params['remarks'],
                'tabbr' => $pinyin->abbr($params['templateName']),
                'primaryKey' => $params['primaryKey'],
                'myData' => $params['myData'],
                'endTime' => strtotime($params['endTime']),
            ];

            unset($params['templateName']);
            unset($params['primaryKey']);
            unset($params['myData']);
            unset($params['remarks']);
            unset($params['endTime']);

            #模板名是否重复
            $validate = new \app\admin\validate\Templates();
            if (!$validate->scene('hand')->check($tInfo)) {
                return $this->error($validate->getError());
            }

            $template = model('templates')
                ->where('tid', $tInfo['tid'])
                ->find();
            if ($template['status'] == '1') {
                $this->error("模板已经初始化，不可更改");
            }

            $tInfo['params'] = $params;

            $res = model('Templates')->createByHand($tInfo);
            // return $res;
            if ($res == 1) {
                session('tInfo', null);
                session('optionList', null);
                session('excelData', null);
                $this->success("模板初始化成功", 'admin/Templates/list');
            } else {
                $this->error($res);
            }
        }
    }
}
