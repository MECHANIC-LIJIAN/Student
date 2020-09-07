<?php

namespace app\admin\controller;

class Import extends Base
{
    public function First()
    {
        if (request()->isAjax()) {
            header("content-type:text/html;charset=utf-8");

            $tInfo = [
                'tid' => my_uuid(),
                'uid' => session('admin.id'),
                'tname' => input('post.tname'),
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
        #获取显示在页面的数据列表
        $optionList = model('Templates')->getOptionList();

        $keys = [];
        foreach ($optionList as $k => $v) {
            if (!isset($v['options']) && $v['rule'] != 'text') {
                $keys[$k] = $v['title'];
            }
        }

        $this->assign([
            'keys' => $keys,
            'optionList' => $optionList,
        ]);
        return view();

    }
}
