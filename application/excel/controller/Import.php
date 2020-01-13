<?php

namespace app\excel\controller;

use Env;
use Overtrue\Pinyin\Pinyin;
use think\Controller;
use think\Db;
use think\facade\Request;
use think\Model;

class Import extends Controller
{
    public function index()
    {
        return view();
    }

    public function createTemplateFirst()
    {
        return view();
    }

    public function upload()
    {
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $file = request()->file('tempalte');
        $tname = Request::param('tname');
        // $user=Request::param('user');

        $validate = new \app\excel\validate\Import;
        if (!$validate->scene('upload')->check(['tname' => $tname, 'template' => $file])) {
            $this->error($validate->getError());
        }

        //将文件保存到public/uploads目录下面
        $info = $file->validate(['size' => 1048576, 'ext' => 'xls,xlsx'])->move('./uploads');

        if ($info) {
            //获取上传到后台的文件名
            $fileName = $info->getSaveName();
            //获取文件路径
            $filePath = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $fileName;
        } else {
            $this->error('文件过大或格式不正确导致上传失败-_-!');
        }

        $tInfo = [
            'tId' => uuid(),
            'user' => 'lijian',
            'tName' => $tname,
            'filePath' => str_replace("\\", "/", $info->getPathName()),
            'fileName' => $info->getInfo()['name'],
        ];
        session('tInfo', $tInfo);
        return $this->success("模板文件上传成功！");
    }

    /**
     * 查看模板文件
     * @return void 读取结果
     */
    public function createTemplateSecond()
    {
        $tInfo = session('tInfo');
        $data = readExcel($tInfo['filePath']);
        end($data);
        $finalKey = key($data);
        
        $optionList = array();
        $tableField = array();
        $pinyin = new Pinyin();

        for ($col = 'A'; $col != $finalKey; $col++) {
            $option = 'option_' . $col;
            for ($row = 1; $row <= count($data[$col]); $row++) {
                $tmp = array();
                if ($row == 1) {
                    $tmp['tid'] = $tInfo['tId'];
                    $tmp['pid'] = '0';
                    $tmp['sid'] = $option;
                    $tmp['type'] = 'p';
                    $tmp['content'] = $data[$col][$row];
                    $abbr = $pinyin->abbr($data[$col][1]);
                    $tableField[$option] = $data[$col][1];
                } else {
                    $tmp['tid'] = $tInfo['tId'];
                    $tmp['pid'] = $option;
                    $tmp['sid'] = $option . "_" . $row;
                    $tmp['type'] = 'c';
                    $tmp['content'] = $data[$col][$row];
                }
                $optionList[] = $tmp;
            }
        }

        session('optionList', $optionList);
        $optionList = getOptionList($optionList, $pid = 'pid', $id = 'sid');
        dump($optionList);
        return $this->fetch('create_template_second', ['optionList' => $optionList, 'tname' => $tInfo['tName'], 'tableField' => $tableField]);

        if (request()->isAjax()) {

            $tInfo = session('tInfo');
            $data = session("optionList");

            $pinyin = new Pinyin();

            $template = model('templates')
                ->where('tid', $tInfo['tId'])
                ->find();

            if ($template['status'] == '1') {
                $this->error("模板已经初始化，不可更改");
            } else {
                $pinyin = new Pinyin();
                $template = new \app\excel\model\Templates;
                $template->tid = $tInfo['tId'];
                $template->tuser = $tInfo['user'];
                $template->tname = $tInfo['tName'];
                $template->tfilename = $tInfo['fileName'];
                $template->tfilepath = $tInfo['filePath'];
                $template->tabbr = $pinyin->abbr($tInfo['tName']);
                $template->status = '1';
                $template->primaryKey = input('post.primaryKey');
                $template->ifUseXh = input('post.ifUseXH');
                $template->save();
            }
            $res2 = model("TemplatesOption")->isUpdate(false)->saveAll($data);
            Db::name("templates_sum")->where('id', 1)->setInc('count');
            if ($res2) {
                $this->success("模板初始化成功", "excel/import/createtemplateThird");
            } else {
                $this->error("模板初始化失败");
            }
        }

    }

    public function createtemplatethird()
    {
        $shareUrl = url('excel/import/fill', ['id' => session("templateId")]);
        $this->assign("shareUrl", $shareUrl);
        return view();
    }
}
