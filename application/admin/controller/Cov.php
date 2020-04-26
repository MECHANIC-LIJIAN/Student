<?php

namespace app\admin\controller;

use app\admin\model\Cov as ModelCov;
use app\admin\model\CovReports;
use think\Db;
use ZipArchive;

class Cov extends Base
{
    /**
     * 报告列表
     */
    public function index()
    {

        $reportList = Db::name('cov')->field('id,title,status,date')->order('date', 'desc')->select();

        $this->assign([
            'token' => time(),
        ]);
        if (in_array(9, $this->groupIds)) {
            return view('index', ['datas' => $reportList]);
        } else {
            $reportedDate = Db::name('cov_reports')->where(['uid' => $this->uid])->field('date')->column('date');
            foreach ($reportList as $k => $v) {
                if (in_array($v['date'], $reportedDate)) {
                    $reportList[$k]['ifReport'] = 1;
                }
            }

            return view('index_b', ['datas' => $reportList]);
        }

    }

    /**
     * 新建报告
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
                $this->success('今日报告创建成功',url("admin/cov/index"));
            } else {
                $this->error('创建失败');
            }
        }
    }

    /**
     * ，每日报告列表
     *
     * @return void
     */
    public function perDayReports()
    {

        $list = model('cov_reports')->where(['date' => input('date')])->field('id,uid,date,report_pic_path,phone_pic_path')->paginate(5);

        $oneReport = Db::name('cov')->where(['date' => input('date')])->field('title,date')->find();
        foreach ($list as $k => $v) {
            $v['phone_pic_path'] = explode('|', trim($v['phone_pic_path'], "|"));
        }
        $this->assign([
            'datas' => $list,
            'title' => $oneReport['title'],
            'date' => $oneReport['date'],
        ]);

        return view();
    }

    /**
     * 上报页面
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
                $this->success('提交成功', url('admin/cov/index'));
            } else {
                $this->error('提交失败');
            }
        }

        $reportDatas = [
            'uid' => $this->uid,
            'date' => input('date'),
            'pic_date' => date("m.d", input('date')),
        ];
        cookie('reportDatas', $reportDatas);

        $this->assign([
            'token' => time(),
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
        $type = input('post.type');
        $n = 2;
        $files = request()->file('file_pic');
        $reportDatas = cookie('reportDatas');

        if ($type === 's') {
            $imgName = $reportDatas['pic_date'] . '-' . session('admin.username') . '1.png';
            $info = $files->validate(['size' => 1920 * 1920, 'ext' => 'jpg,png'])->move('uploads/cov/2020/' . $reportDatas['pic_date'], $imgName, true);

            if (!$info) {
                $this->error('上传失败');
            }

            $reportDatas['report_pic_path'] = $info->getPathName();
            cookie('reportDatas', $reportDatas);
        } else {

            $phonePicPath = '';
            foreach ($files as $file) {
                $imgName = $reportDatas['pic_date'] . '-' . session('admin.username') . $n . '.png';
                $info = $file->validate(['size' => 1920 * 1920, 'ext' => 'jpg,png'])->move('uploads/cov/2020/' . $reportDatas['pic_date'], $imgName, true);
                $n += 1;

                if (!$info) {
                    $this->error('上传失败');
                }

                $phonePicPath = $phonePicPath . $info->getPathName() . '|';

            }

            $reportDatas['phone_pic_path'] = $phonePicPath;
            cookie('reportDatas', $reportDatas);
        }

        // 成功上传后 获取上传信息
        $this->success('上传成功');
    }

    public function downPerDayReports()
    {

        $date = date("m.d", input('date'));
        $path = env('ROOT_PATH').'uploads/cov/2020/';
        $zipFile = $date . '.zip';
        $picPath = $path . $date;

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
