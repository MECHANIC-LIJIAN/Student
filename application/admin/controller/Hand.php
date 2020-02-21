<?php

namespace app\admin\controller;

use Overtrue\Pinyin\Pinyin;
use think\facade\Request;
use think\Db;

class Hand extends Base
{
    public function index()
    {
        return view();
    }

    public function add()
    {
        if (request()->isAjax()) {
            $params = Request::post();

            if (!array_key_exists('primaryKey',$params)) {
                $this->error("请至少添加一个字段");
            }
            $tId = uuid();
            $template = model('templates')
                ->where('tid', $tId)
                ->find();

            if ($template['status'] == '1') {
                $this->error("模板已经初始化，不可更改");
            }
            $pinyin = new Pinyin();
            $template = new \app\admin\model\Templates;
            $template->tid = $tId;
            $template->tuser = session('admin.id');
            $template->tname = $params['templateName'];
            $template->tabbr = $pinyin->abbr($params['templateName']);
            $template->status = '1';
            $template->primaryKey = $params['primaryKey'];
            $res = $template->save();

            unset($params['templateName']);
            unset($params['primaryKey']);

            
            $data = [];
            foreach ($params as $key => $value) {
                $temp = explode("_", $key);
                if (count($temp) == 2) {
                    $field = [];
                    $field['tid'] = $tId;
                    $field['pid'] = '0';
                    $field['sid'] = $key;
                    $field['type'] = 'p';
                    $field['title'] = $value;
                    $field['rule'] = $params[$key."_rule"];
                    $abbr = $pinyin->abbr($value);
                    $data[] = $field;
                } elseif ($temp[2] != "rule") {
                    $field = [];
                    $pId=implode("_",array_splice($temp,0,2));
                    $field['tid'] = $tId;
                    $field['pid'] = $pId;
                    $field['sid'] = $key;
                    $field['type'] = 'c';
                    $field['title'] = $value;
                    $field['rule'] = $params[$pId."_rule"];
                    $abbr = $pinyin->abbr($value);
                    $data[] = $field;
                }
            }
            
            // return $data;
            $res = model("TemplatesOption")->isUpdate(false)->saveAll($data);
            $res2 = Db::name("templates_sum")->where('id', 1)->setInc('count');
            if ($res2) {
                $this->success("模板初始化成功",'admin/Templates/list');
            } else {
                $this->error("模板初始化失败");
            }
        }
    }
}
