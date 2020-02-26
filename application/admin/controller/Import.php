<?php

namespace app\admin\controller;

use Env;
use Overtrue\Pinyin\Pinyin;
use think\Db;

class Import extends Base
{
    public function createTemplateFirst()
    {
        return view();
    }

    public function upload()
    {
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $data['tName']=input('post.tname');
        $data['tFile']=input('file.tempalte');

        $validate = new \app\admin\validate\Templates();
        if (!$validate->scene('upload')->check($data)) {
            $this->error($validate->getError());
        }
        
        $tInfo = [
            'tId' => uuid(),
            'user' => session('admin.id'),
            'tName' => $data['tName'],
        ];
        session('tInfo', $tInfo);

        $res =model('Templates')->getDataByFile($data);
        if ($res!=1) {
            $this->error($res);
        }

        return $this->success("表单文件上传成功！",url('admin/Import/createTemplateSecond'));
    }

    /**
     * 查看表单文件
     * @return void 读取结果
     */
    public function createTemplateSecond()
    {
        // if (!session('?tInfo')) {
        //     $this->redirect('admin/Import/createTemplateFirst');
        // }

        if (request()->isAjax()) {

            $tInfo = session('tInfo');
            $data = session("optionList");

            $template = model('templates')
                ->where('tid', $tInfo['tId'])
                ->find();

            if ($template['status'] == '1') {
                $this->error("表单已经初始化，不可更改");
            }
            $pinyin = new Pinyin();
            $template = new \app\admin\model\Templates;
            $template->tid = $tInfo['tId'];
            $template->tuser = $tInfo['user'];
            $template->tname = $tInfo['tName'];
            $template->tfilename = $tInfo['fileName'];
            $template->tfilepath = $tInfo['filePath'];
            $template->tabbr = $pinyin->abbr($tInfo['tName']);
            $template->status = '1';
            $template->primaryKey = input('post.primaryKey');
            $template->myData = input('post.myData');
            $template->ifUseData = input('post.ifUseData');
            $res = $template->save();

            $res2 = model("TemplatesOption")->isUpdate(false)->saveAll($data);
            Db::name("templates_sum")->where('id', 1)->setInc('count');
            if ($res2) {
                $this->success("表单初始化成功", "admin/import/createtemplateThird");
            } else {
                $this->error("表单初始化失败");
            }
        }

        $tInfo = session('tInfo');
        $data = session('tData');
        end($data);
        $finalKey = key($data);
        $optionList = [];
        $tableField = [];
        $pinyin = new Pinyin();

        for ($col = 'A'; $col <= $finalKey; $col++) {
            $option = 'option_' . $col;
            for ($row = 1; $row <= count($data[$col]); $row++) {
                $tmp = [];
                if ($row == 1) {
                    $tmp['tid'] = $tInfo['tId'];
                    $tmp['pid'] = '0';
                    $tmp['sid'] = $option;
                    $tmp['type'] = 'p';
                    $tmp['content'] = $data[$col][$row];
                    $tmp['abbr'] = $pinyin->abbr($data[$col][1]);
                    $tableField[$option] = $data[$col][1];
                } else {
                    $tmp['tid'] = $tInfo['tId'];
                    $tmp['pid'] = $option;
                    $tmp['sid'] = $option . "_" . $row;
                    $tmp['type'] = 'c';
                    $tmp['content'] = $data[$col][$row];
                    $tmp['abbr'] = $pinyin->abbr($data[$col][1]);
                }
                $optionList[] = $tmp;
            }
        }

        session('optionList', $optionList);

        $optionList = getOptionList($optionList, $pid = 'pid', $id = 'sid');

        return $this->fetch('create_template_second', ['optionList' => $optionList, 'tname' => $tInfo['tName'], 'tableField' => $tableField]);

    }

    public function createTemplateThird()
    {
        if (!session('?tInfo')) {
            $this->redirect('admin/Import/createTemplateFirst');
        }

        
        $shareUrl = url('index/Template/readTemplate', ['id' => session('tInfo')['tId']],'',true);
        $this->assign("shareUrl", $shareUrl);
        session('tInfo',null);
        session('optionList',null);
        return view();
    }
}
