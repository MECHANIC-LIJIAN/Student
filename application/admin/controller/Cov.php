<?php

namespace app\admin\controller;

use app\admin\model\Cov as ModelCov;
use app\admin\model\CovReports;
use Exception;
use think\Db;
use think\Image;
use ZipArchive;

class Cov extends Base
{
    /**
     * 辅导员-报告列表
     */
    public function index()
    {

        $reportList = Db::name('cov')->field('id,title,status,date')->order('date', 'desc')->select();

        $this->assign([
            'token' => time(),
            'datas' => $reportList,
        ]);

        return view();
    }

    /**
     * 辅导员-新建报告
     *
     * @return void
     */
    public function newReport()
    {
        $data = [
            'title' => date("m") . '月' . date("d") . '日' . '报告',
            'date' => strtotime(date('Y-m-d')),
            'status' => 0,
            'create_time' => time(),
        ];

        $cov = new ModelCov();
        $res = $cov->getByDate($data['date']);
        if ($res) {
            $this->success('今日报告已创建');
        } else {
            $res = $cov->save($data);
            if ($res) {
                $this->success('今日报告创建成功', url("admin/cov/index",['sid'=>15]));
            } else {
                $this->error('创建失败');
            }
        }
    }

    /**
     * 辅导员-每日报告列表
     *
     * @return void
     */
    public function perDayReports()
    {

        $list = model('cov_reports')->where(['date' => input('date')])->with('getProfile')->field('id,uid,date,report_pic_path,phone_pic_path')->paginate(5);

        foreach ($list as $k => $v) {
            $picList = explode('|', trim($v['phone_pic_path']));
            array_pop($picList);
            $v['phone_pic_path'] = $picList;
        }

        $oneReport = Db::name('cov')->where(['date' => input('date')])->field('title,date')->find();

        $this->assign([
            'datas' => $list,
            'title' => $oneReport['title'],
            'date' => $oneReport['date'],
        ]);

        return view();
    }

    /**
     * 班长-报告记录
     *
     * @return void
     */
    public function indexB()
    {
        $reportList = Db::name('cov')->field('id,title,status,date')->order('date', 'desc')->select();
        $reportedDate = Db::name('cov_reports')->where(['uid' => $this->uid])->field('date')->column('date');
        foreach ($reportList as $k => $v) {
            if (in_array($v['date'], $reportedDate)) {
                $reportList[$k]['ifReport'] = 1;
            }
        }

        return view('index_b', ['datas' => $reportList]);
    }

    /**
     * 班长-上报页面
     *
     * @return void
     */
    public function reporter()
    {
        if (request()->isAjax()) {
            $reportDatas = cookie('reportDatas');

            $covReports = new CovReports();
            $res = $covReports->allowField(true)->save($reportDatas);
            if ($res) {
                $this->success('提交成功', url('admin/cov/indexB',['sid'=>15]));
            } else {
                $this->error('提交失败');
            }
        }

        $reportDatas = [
            'uid' => $this->uid,
            'date' => input('date'),
            'pic_date' => date("n.j", input('date')),
        ];
        cookie('reportDatas', $reportDatas);

        $this->assign([
            'token' => time(),
            'title' => date("m.d", input('date')) . "报告",
        ]);
        return view();
    }

    /**
     * 图片上传接口
     *
     * @return void
     */
    public function ajaxUpload()
    {

        $reportDatas = cookie('reportDatas');
        $imgPath = 'uploads/cov/org/';
        $subImgPath = 'uploads/cov/' . $reportDatas['pic_date'] . "/";

        $textSize = 50;
        $textColor = '#FF3333';
        $textLocate = \think\Image::WATER_EAST;
        $textOffset=[-100,0];
        $textAngle = 0;

        $type = input('post.type');
        $files = request()->file('file_pic');

        if (!$files) {
            $this->error('没有文件被上传');
        }

        if ($type === 's') {

            try {
                mkdir($subImgPath, 0755, true);
                #保存原图
                $info = $files->validate(['size' => 4000 * 4000, 'ext' => 'jpg,png'])->move($imgPath);

                if (!$info) {
                    $this->error('上传失败');
                }
                #打开原图
                $image = \think\Image::open($imgPath . "/" . $info->getSaveName());

                #拼接图片名
                $imgName = $reportDatas['pic_date'] . '-' . session('admin.username') . '1' . "." . $image->type();
                
                #添加水印
                $image->text($reportDatas['pic_date'], getcwd() . '/msyh.ttf', $textSize, $textColor, $textLocate, $textOffset, $textAngle)->save($subImgPath . $imgName);
              
            } catch (Exception $e) {
                $this->error('上传失败');
            }

            $reportDatas['report_pic_path'] ="/". $subImgPath . $imgName;

            cookie('reportDatas', $reportDatas);
        } else {

            $n = 2;
            $phonePicPath = '';
            foreach ($files as $file) {
                try {
                    #保存原图
                    $info = $file->validate(['size' => 4000 * 4000, 'ext' => 'jpg,png'])->move($imgPath);

                    #打开原图
                    $image = \think\Image::open($imgPath . "/" . $info->getSaveName());

                    #拼接图片名
                    $imgName = $reportDatas['pic_date'] . '-' . session('admin.username') . $n . "." . $image->type();

                    #添加水印
                    $image->text($reportDatas['pic_date'], getcwd() . '/msyh.ttf', $textSize, $textColor, $textLocate, $textOffset, $textAngle)->save($subImgPath . $imgName);

                } catch (Exception $e) {
                    $this->error('上传失败');
                }
                $phonePicPath = $phonePicPath ."/". $subImgPath . $imgName . '|';
                $n+=1;
            }

            
            $reportDatas['phone_pic_path'] = $phonePicPath;

            cookie('reportDatas', $reportDatas);
        }

        // 成功上传后 获取上传信息
        $this->success('上传成功');
    }

    public function downPerDayReports()
    {

        $date = date("n.j", input('date'));
        $path = env('ROOT_PATH') . 'public/uploads/cov/';
        $zipFile = $date . '.zip';
        $picPath = $path . $date;

        // halt($picPath);
        $zip = new ZipArchive();
        $overwrite = false;
        if ($zip->open($path . $zipFile, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return "无法下载";
        }
        $handler = opendir($picPath); //打开当前文件夹由$path指定。

        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..'，不要对他们进行操作
                //将文件加入zip对象
                $zip->addFile($picPath . "/" . $filename, $filename);
            }
        }
        closedir($path);
        $zip->close();
        $filename = $path . $zipFile;
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($filename)); //文件名
        header("Content-Type: application/zip"); //zip格式的
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        header('Content-Length: ' . filesize($filename)); //告诉浏览器，文件大小
        ob_clean();
        flush();
        @readfile($filename);

    }
}
