<?php

namespace app\admin\controller;

class Cov extends Base
{

    public function index()
    {

        return view();
    }

    public function single()
    {
        return view();
    }

    public function ajaxUpload()
    {
        if (request()->isPost()) {

            $imgName=date("m.d").'-'.session('admin.username').'.png';
            
            $file = request()->file('file_data');

            $info = $file->validate(['size' => 1024 * 1024, 'ext' => 'jpg,png'])->move('uploads/cov/'.date("Ymd"),$imgName,false,true);

            if ($info) {
                // 成功上传后 获取上传信息
                return [
                    'type' => $info->getExtension(),
                    'path' => $info->getSaveName(),
                ];
            } else {
                // $imgPath='uploads/cov/'.date("Ymd").'/'.$imgName;
                // $image = \think\Image::open($file);
                // $image->thumb(1024,1024)->save($imgPath);
                // return $image;
                // 上传失败获取错误信息
                // echo $file->getError();
                return '上传失败';
            }
        }
    }
}
