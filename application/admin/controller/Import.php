<?php

namespace app\admin\controller;

class Import extends Base
{
    public function First()
    {
        if (request()->isAjax()) {
            header("content-type:text/html;charset=utf-8");

            $tInfo = [
                'tid'=>my_uuid(),
                'uid' => session('admin.id'),
                'tname' => input('post.tname'),
                'remarks' => input('post.remarks', ""),
                'tFile' => input('file.tempalte'),
            ];

            #验证文件类型 模板名是否重复
            $validate = new \app\admin\validate\Templates();
            if (!$validate->scene('upload')->check($tInfo)) {
                return $this->error($validate->getError());
            }
            #读文件 存到session
            $res = model('Templates')->uploadTempate($tInfo);

            if ($res == 1) {
                return $this->success("表单文件上传成功！", url('admin/Import/Second'));
            } else {
                return $this->error($res);
            }
        }
        $this->assign([
            'showKeys' => 'true',
        ]);
        return view();
    }

    /**
     * 查看表单文件
     * @return void 读取结果
     */
    public function Second()
    {
        if (request()->isAjax()) {

            $tInfo = session('tInfo');
            $tInfo['primaryKey'] = input('post.primaryKey', "");
            $tInfo['myData'] = input('post.myData', 0);
            $tInfo['endTime'] = strtotime(input('post.endTime'));
            // halt($tInfo);
            $res = model('Templates')->createByFile($tInfo);

            if ($res == 1) {
                session('tInfo', null);
                session('optionList', null);
                session('excelData', null);
                $this->success("表单初始化成功", "admin/Templates/list");
            } else {
                $this->error($res);
            }
        }

        #读取模板信息
        $tInfo = session('tInfo');
        #获取显示在页面的数据列表
        $optionList = model('Templates')->getOptionList($tInfo);

        $keys = [];
        foreach ($optionList as $k => $v) {
            if (!isset($v['options']) && $v['rule'] != 'text') {
                $keys[$k] = $v['title'];
            }
        }
        $this->assign([
            'keys' => $keys,
            'optionList' => $optionList,
            'tname' => $tInfo['tname'],
        ]);
        return view();

    }

    // public function Third()
    // {
    //     if (!session('?tInfo')) {
    //         $this->redirect('admin/Import/First');
    //     }

    //     $shareUrl = url('index/Template/readTemplate', ['id' => session('tInfo')['tid']], '', true);
    //     $this->assign("shareUrl", $shareUrl);
    //     session('tInfo', null);
    //     session('optionList', null);
    //     session('excelData', null);
    //     return view();
    // }
}
