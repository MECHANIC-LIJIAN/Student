<?php

namespace app\admin\controller;

use app\admin\model\Cov as ModelCov;
use app\admin\model\CovReports;
use Exception;
use Overtrue\Pinyin\Pinyin;
use think\Db;
use think\facade\Log;
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
                $this->success('今日报告创建成功', url("admin/cov/index", ['sid' => 15]));
            } else {
                $this->error('创建失败');
            }
        }
    }

    public function addMembers()
    {

        if (request()->isAjax()) {

            $data = input('post.');
            $emailList = explode("\r\n", $data['memberList']);

            $members = Db::name('admin')->where('email', 'in', $emailList)->field('id,username')->select();

            foreach ($members as $k => $v) {
                $members[$k]['uid'] = $members[$k]['id'];
                unset($members[$k]['id']);
                $members[$k]['role_id'] = 3;
                $members[$k]['pid'] = (int) $data['pid'];
                $members[$k]['create_time'] = time();
            }

            $res = Db::name("cov_users")->strict(false)->insertAll($members);

            if ($res) {
                $this->success('提交成功', url('admin/cov/index', ['sid' => 15]));
            } else {
                $this->error('提交失败');
            }
        }

        $instructorIds = Db::name("auth_group_access")->where(['group_id' => 9])->column('uid');
        $instructor = model("admin")->where('id', 'in', $instructorIds)->field('id,username')->select();

        $cadreIds = Db::name("auth_group_access")->where(['group_id' => 10])->column('uid');
        $cadre = model("admin")->where('id', 'in', $cadreIds)->field('id,username')->select();

        $roles = [
            'instructor' => $instructor,
            'cadre' => $cadre,
        ];
        cookie('roles', $roles);
        $this->assign([
            'instructor' => $instructor,
            'cadre' => $cadre,
        ]);

        return view();
    }

    /**
     * 辅导员-每日报告列表
     *
     * @return void
     */
    public function perDayReports()
    {
        $myTeam = Db::name("cov_users")->where(['pid' => $this->uid])->field('uid,username')->select();

        if (in_array(9, $this->groupIds)) {
            $this->assign([
                'ifInstroctor' => 'yes',
            ]);
        } else {
            $this->assign([
                'ifInstroctor' => 'no',
            ]);
        }

        $hasList = model('cov_reports')
            ->where(['date' => input('date')])
            ->whereIn('uid', array_column($myTeam, 'uid'))
            ->with('getProfile')
            ->field('id,uid,date,report_pic_path,phone_pic_path')
            ->paginate(10);

        $report_pic_path = dirname($hasList[0]['report_pic_path']);
        $phone_pic_path = dirname(dirname($hasList[0]['report_pic_path'])) . "/phone";

        foreach ($hasList as $k => $v) {
            $picList = explode('|', trim($v['phone_pic_path']));

            array_pop($picList);
            $v['phone_pic_path'] = $picList;
            foreach ($myTeam as $mk => $mv) {
                if ($v['get_profile']['id'] == $mv['uid']) {
                    unset($myTeam[$mk]);
                }
            }
        }

        $oneReport = Db::name('cov')->where(['date' => input('date')])->field('title,date')->find();

        $this->assign([
            'hasList' => $hasList,
            'notList' => $myTeam,
            'title' => $oneReport['title'],
            'date' => $oneReport['date'],
            'report_pic_path' => $report_pic_path,
            'phone_pic_path' => $phone_pic_path,
            'date_pic_path' => dirname($report_pic_path),
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
                $this->success('提交成功', url('admin/cov/indexB', ['sid' => 15]));
            } else {
                $this->error('提交失败');
            }
        }
        $instructorIds = Db::name("auth_group_access")->where(['group_id' => 9])->column('uid');
        $pids = Db::name('cov_users')->where('uid', $this->uid)->column('pid');
        unset($pids[array_search(1, $pids)]);
        $instructorId = array_intersect($pids, $instructorIds);

        $instructorId = implode("", $instructorId);

        // dump([$instructorIds,$pids,$instructorId]);
        if (empty($instructorId) || $instructorId === "") {
            $this->error('还没有为您分配所属辅导员');
        }

        $instructor = Db::name("admin")->where('id', '=', $instructorId)->field('id,username')->find();

        $pinyin = new Pinyin();
        $pathPriex = $pinyin->permalink($instructor['username'], '');

        $reportDatas = [
            'uid' => $this->uid,
            'date' => input('date'),
            'pic_date' => date("n.j", input('date')),
            'pathPriex' => $pathPriex,
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
        $saveImgPath = 'uploads/cov/' . $reportDatas['pathPriex'] . '/' . $reportDatas['pic_date'] . "/";

        $text="2020.".$reportDatas['pic_date'];
        $textSize = 50;
        $textColor = '#FF3333';
        $textLocate = \think\Image::WATER_EAST;
        $textOffset = [-100, 0];
        $textAngle = 0;

        $type = input('post.type');
        $files = request()->file('file_pic');

        if (!$files) {
            $this->error('没有文件被上传');
        }

        if ($type === 's') {

            try {
                $saveImgPath = $saveImgPath . 'condition/';
                if (!is_dir($saveImgPath)) {
                    mkdir($saveImgPath, 0755, true);
                }
                #保存原图
                $info = $files->validate(['size' => 4000 * 4000, 'ext' => 'jpg,png'])->move($imgPath);

                if (!$info) {
                    $this->error('上传失败');
                }

                #计算图片hash值
                $orgImgPath = $imgPath . $info->getSaveName();
                $order = "python3 tu.py " . $orgImgPath . " 2>&1";

                exec($order, $output, $return);

                if ($return != 0) {
                    Log::error($output);
                }
                
                #检测是否存在该图片
                $res = Db::name('cov_pic_hash')->getByHash($output[0]);
                if ($res) {
                    $this->error('系统中已有该图片！');
                }

                Db::name('cov_pic_hash')->insert(['hash' => $output[0]]);
                #打开原图
                $image = \think\Image::open($imgPath . "/" . $info->getSaveName());

                #拼接图片名
                $imgName = $reportDatas['pic_date'] . '-' . session('admin.username') . '1' . "." . $image->type();

                $saveImgPath = $saveImgPath . $imgName;

                #添加水印
               
                $image->text($text, getcwd() . '/msyh.ttf', $textSize, $textColor, $textLocate, $textOffset, $textAngle)->save($saveImgPath);

                $reportDatas['report_pic_path'] = "/" . $saveImgPath;

            } catch (Exception $e) {

                $this->error("上传失败");
            }

            cookie('reportDatas', $reportDatas);
        } else {

            $n = 2;
            $phonePicPath = '';
            $saveImgPath = $saveImgPath . 'phone/';
            if (!is_dir($saveImgPath)) {
                mkdir($saveImgPath, 0755, true);
            }
            try {

                foreach ($files as $file) {
                    #保存原图
                    $info = $file->validate(['size' => 4000 * 4000, 'ext' => 'jpg,png'])->move($imgPath);

                    if (!$info) {
                        $this->error('上传失败');
                    }

                    #计算图片hash值
                    $orgImgPath = $imgPath . $info->getSaveName();
                    $order = "python3 tu.py " . $orgImgPath . " 2>&1";

                    exec($order, $output, $return);

                    if ($return != 0) {
                        Log::error($output);
                    }

                    #检测是否存在该图片
                    $res = Db::name('cov_pic_hash')->getByHash($output[0]);
                    if ($res) {
                        $this->error('系统中已有该图片！');
                    }

                    $res = Db::name('cov_pic_hash')->insert(['hash' => $output[0]]);

                    #打开原图
                    $image = \think\Image::open($imgPath . "/" . $info->getSaveName());

                    #拼接图片名
                    $imgName = $reportDatas['pic_date'] . '-' . session('admin.username') . $n . "." . $image->type();

                    $saveImgName = $saveImgPath . $imgName;

                    #添加水印
                    $image->text($text, getcwd() . '/msyh.ttf', $textSize, $textColor, $textLocate, $textOffset, $textAngle)->save($saveImgName);

                    $phonePicPath = $phonePicPath . "/" . $saveImgName . '|';
                    $n += 1;
                }

                $reportDatas['phone_pic_path'] = $phonePicPath;
            } catch (Exception $e) {
                
                $this->error("上传失败");
            }

            cookie('reportDatas', $reportDatas);
        }

        // 成功上传后 获取上传信息
        $this->success('上传成功');
    }

    public function downPerDayReports()
    {

        $type = input('type');
        $date = date('n.j', input('date'));

        $path = env('ROOT_PATH') . 'public' . input('path');

        $picPath = $path;
        if ($type == 'all') {
            $zipFile = basename($path) . '.zip';
            $zipPath = dirname($picPath) . '/' . $zipFile;
        } else {
            $zipFile = $date . '-' . basename($path) . '.zip';
            $zipPath = dirname(dirname($picPath)) . '/' . $zipFile;
        }

        $zip = new ZipArchive();

        $overwrite = false;
        if ($zip->open($zipPath, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return "无法下载";
        }

        $handler = opendir($picPath); //打开当前文件夹由$path指定。

        addFilesToZip($handler, $zip, $picPath);
        // while (($filename = readdir($handler)) !== false) {
        //     if ($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..'，不要对他们进行操作
        //         //将文件加入zip对象
        //         $zip->addFile($picPath . "/" . $filename, $filename);
        //     }
        // }
        $zip->close();

        closedir($picPath);
        $zip->close();
        $filename = $zipPath;
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
