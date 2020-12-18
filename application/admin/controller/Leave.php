<?php

namespace app\admin\controller;

use app\admin\model\CovLeave;
use app\admin\model\CovLeaveReports;
use Exception;
use myredis\Redis;
use Overtrue\Pinyin\Pinyin;
use PhpOffice\PhpWord\PhpWord;
use think\Db;
use think\facade\Log;
use think\Image;
use ZipArchive;

class Leave extends Base
{

    public function initialize()
    {
        parent::initialize();

        $redis = new Redis();
        $redisKey = strtotime(date('Y-m-d')) . "_leave_record";
        if (!$redis->exists($redisKey)) {
            $this->newReport();
        }
    }
    /**
     * 辅导员-报告列表
     */
    public function index()
    {
        $reportList = Db::name('cov_leave')->field('id,title,status,date')->order('date', 'desc')->select();

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
            'title' => date("m") . '月' . date("d") . '日' . '出行情况排查报告',
            'date' => strtotime(date('Y-m-d')),
            'status' => 0,
            'create_time' => time(),
        ];

        $cov = new CovLeave();
        $res = $cov->field('id')->getByDate($data['date']);

        if ($res) {
            $redis = new Redis();
            $redisKey = strtotime(date('Y-m-d')) . "_leave_record";
            $redis->set($redisKey, $redisKey);
            $redis->expire($redisKey, 60 * 60 * 16);
            return [
                'code' => 1,
                'msg' => "今日出行情况排查报告已创建",
                'url' => url("admin/leave/index", ['sid' => 15]),
            ];
        } else {
            $res = $cov->save($data);
            if ($res) {
                return [
                    'code' => 1,
                    'msg' => "今日出行情况排查报告创建成功",
                    'url' => url("admin/leave/index", ['sid' => 15]),
                ];
            } else {
                $this->error('创建失败');
            }
        }
    }

    public function delOneReport()
    {
        if (request()->isAjax()) {
            $reportId = input('post.id');
            $oneReport = model('cov_leave_reports')->where(['id' => $reportId])->field('id,uid,date,picPath')->find();

            try {
                $publicPath = env('ROOT_PATH') . "public";
                unlink($publicPath . $oneReport['picPath']);
            } catch (Exception $e) {
                Log::write($e->getMessage(), 'error');
            }

            $oneReport->delete(true);
            $this->success("删除成功");
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
        $where = ['pid' => $this->uid];
        if (input('key') == 'admin') {
            $where = ['pid' => input('uid')];
        }
        $myTeam = Db::name("cov_users")->where($where)->distinct(true)->field('uid,username')->order(['username' => 'asc'])->select();

        $hasList = model('cov_leave_reports')
            ->where(['date' => input('date')])
            ->whereIn('uid', array_column($myTeam, 'uid'))
            ->with('getProfile')
            ->field('id,uid,date,picPath,todayCondition')
            ->select()
            ->toArray();

        $oneReport = Db::name('cov_leave')->where(['date' => input('date')])->field('title,date')->find();

        #判断角色
        if (in_array(9, $this->groupIds)) {
            #是辅导员
            $this->assign([
                'ifInstroctor' => 'yes',
            ]);
        } else {
            $this->assign([
                'ifInstroctor' => 'no',
            ]);
        }
        $this->assign([
            'hasList' => $hasList,
            // 'notList' => $myTeam,
            'title' => $oneReport['title'],
            'date' => $oneReport['date'],
            'date_pic_path' => dirname($hasList[0]['picPath']),
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
        $reportList = Db::name('cov_leave')->field('id,title,status,date')->order('date', 'desc')->select();
        $reportedDate = Db::name('cov_leave_reports')->where(['uid' => $this->uid])->field('date')->column('date');
        foreach ($reportList as $k => $v) {
            if (in_array($v['date'], $reportedDate)) {
                $reportList[$k]['ifReport'] = 1;
            } else {
                $reportList[$k]['ifReport'] = 0;
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
            $reportDatas = [
                'uid' => $this->uid,
                'date' => input('post.date'),
                'todayCondition' => input('post.todayCondition'),
                'picPath' => input('post.picPath'),
            ];
            if ($reportDatas['todayCondition'] == "") {
                $this->error('请填写具体情况');
            }
            $covLeaveReports = new CovLeaveReports();
            $res = $covLeaveReports->allowField(true)->save($reportDatas);

            if ($res) {
                cookie('reportDatas', null);
                $this->success('提交成功', url('admin/leave/indexB', ['sid' => 15]));
            } else {
                $this->error('提交失败');
            }
        }

        $instructorIds = Db::name("auth_group_access")->where(['group_id' => 9])->column('uid');
        $instructorId = Db::name('cov_users')
            ->where([
                'uid' => $this->uid,
            ])
            ->whereIn('pid', $instructorIds)
            ->distinct(true)
            ->column('pid');

        $instructorId = implode("", delByValue($instructorId, 4));

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
            'title' => date("m.d", input('date')) . "请假情况报告",
            'date' => input('date'),
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

        #每日的上传目录
        $saveImgPath = 'uploads/leave/' . $reportDatas['pathPriex'] . '/' . $reportDatas['pic_date'] . "/";

        #水印
        $text = "2020." . $reportDatas['pic_date'];

        #允许的后缀
        $allowExt = 'jpg,jpeg,tif,gif,png';

        $files = request()->file('single_file_pic');

        if (!$files) {
            $this->error('没有文件被上传');
        }

        !is_dir($saveImgPath) ? mkdir($saveImgPath, 0755, true) : 1;

        try {
            #保存原图
            $info = $files->validate(['size' => 4000 * 4000, 'ext' => $allowExt])->move($imgPath);

            if (!$info) {
                $this->error('上传失败');
            }

            #拼接原始绝对路径
            $orgImgPath = $imgPath . $info->getSaveName();
            #拼接图片名,不带后缀
            $imgName = $reportDatas['pic_date'] . '-' . session('admin.username');

            $saveImgName = $saveImgPath . $imgName;
            #添加水印
            $res = $this->addWater($orgImgPath, $saveImgName, $text);
            $reportDatas['report_pic_path'] = "/" . $res;
        } catch (Exception $e) {
            $this->error("上传失败");
        }
        cookie('reportDatas', $reportDatas);

        // 成功上传后 获取上传信息
        $this->success('上传成功', '/', $reportDatas['report_pic_path']);
    }

    public function downPerDayReports()
    {

        $picPath = env('ROOT_PATH') . 'public' . input('path');
        $zipFile = basename($picPath) . '.zip';
        $zipPath = dirname($picPath) . '/' . $zipFile;

        $hasList = model('cov_leave_reports')
            ->where(['date' => input('date')])
            ->with('getProfile')
            ->field('id,uid,date,picPath,todayCondition')
            ->select()
            ->toArray();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontSize(12);
        $section = $phpWord->addSection();

        foreach ($hasList as $v) {
            $section->addText("报告人：".$v['username']);
            $section->addText("报告内容：");
            $section->addText(str_replace("\n","<w:br />",$v['todayCondition']),array('size'=>11));
            $section->addText("\n\n");
        }
        // halt($hasList);
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

        $objWriter->save($picPath  . '/'.input('date').'生物信息学院本科生出行情况排查报告.docx');

        $zip = new ZipArchive();
        $overwrite = false;
        if ($zip->open($zipPath, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return "无法下载";
        }

        $handler = opendir($picPath); //打开当前文件夹由$path指定。

        addFilesToZip($handler, $zip, $picPath);

        $zip->close();

        closedir($picPath);

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
        unlink($zipPath);
        // } else {
        //     #是学生干部

        //     $where = ['pid' => $this->uid];
        //     if (input('key') == 'admin') {
        //         $where = ['pid' => input('uid')];
        //     }
        //     $myTeam = Db::name("cov_users")->where($where)->field('uid,username')->order(['username' => 'asc'])->select();

        //     $hasList = model('cov_reports')
        //         ->where(['date' => input('date')])
        //         ->whereIn('uid', array_column($myTeam, 'uid'))
        //         ->field('id,uid,date,report_pic_path,phone_pic_path')
        //         ->select()
        //         ->toArray();

        //     foreach ($hasList as $k => &$v) {
        //         $report_pic_path[] = $v['report_pic_path'];
        //         $picList = explode('|', trim($v['phone_pic_path']));
        //         array_pop($picList);
        //         $v['phone_pic_path'] = $picList;
        //         foreach ($picList as $pic) {
        //             $phone_pic_path[] = $pic;
        //         }

        //     }

        //     $date = date('n.j', input('date'));
        //     $path = input('path');

        //     $pic_type = basename($path);

        //     $zipFile = $date . "-" . $pic_type . ".zip";
        //     $zipPath = env('ROOT_PATH') . 'public/uploads/cov/temp/' . $zipFile;

        //     if (!is_dir('uploads/cov/temp/')) {
        //         mkdir('uploads/cov/temp', 0755);
        //     }
        //     // halt([$report_pic_path,$phone_pic_path]);
        //     if ($pic_type == "condition") {
        //         createZip($report_pic_path, $zipPath);
        //     } else {
        //         createZip($phone_pic_path, $zipPath);
        //     }
        //     $filename = $zipPath;
        //     header("Cache-Control: public");
        //     header("Content-Description: File Transfer");
        //     header('Content-disposition: attachment; filename=' . basename($filename)); //文件名
        //     header("Content-Type: application/zip"); //zip格式的
        //     header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        //     header('Content-Length: ' . filesize($filename)); //告诉浏览器，文件大小
        //     ob_clean();
        //     flush();
        //     @readfile($filename);
        //     unlink($zipPath);
        // }
    }

    /**
     * 上传图片的方法
     *
     * @param [string] $imgOrgPath
     * @param [string] $imgSavePath 不带后缀
     * @param string $text
     * @return void
     */
    public function addWater($imgOrgPath, $imgSavePath, $text = '')
    {
        try {
            $textSize = 20;
            $textColor = '#2F4F4F';
            $textLocate = \think\Image::WATER_SOUTHEAST;
            // $textOffset = [-100, 0];
            $textAngle = 0;

            #打开原图
            $image = \think\Image::open($imgOrgPath);

            #确定水印大小和边距
            $imageWidth = $image->width();
            $textSize = 0.05 * $imageWidth;
            $textOffset = [-0.1 * $imageWidth, 0];

            #加上后缀
            $imgSavePath = $imgSavePath . "." . $image->type();

            #添加水印
            $image->text($text, getcwd() . '/msyh.ttf', $textSize, $textColor, $textLocate, $textOffset, $textAngle)->save($imgSavePath);

        } catch (Exception $e) {
            return "添加水印失败，请联系管理员";
        }

        return $imgSavePath;
    }
}
