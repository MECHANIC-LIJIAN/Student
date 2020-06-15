<?php

namespace app\admin\controller;

use Overtrue\Pinyin\Pinyin;

class Hand extends Base
{
    public function index()
    {
        return view();
    }

    public function add()
    {
        if (request()->isAjax()) {

            $params = input('post.');
            if (!array_key_exists('option_1', $params)) {
                $this->error("请至少添加一个字段");
            }

            $pinyin = new Pinyin();
            $tInfo = [
                'tid' => uuid(),
                'uid' => session('admin.id'),
                'tname' => $params['templateName'],
                'remarks' => $params['remarks'],
                'tabbr' => $pinyin->abbr($params['templateName']),
                'primaryKey' => $params['primaryKey'],
                'myData' => $params['myData'],
            ];
            unset($params['templateName']);
            unset($params['primaryKey']);
            unset($params['myData']);
            unset($params['remarks']);

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
