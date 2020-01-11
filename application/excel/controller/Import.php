<?php


namespace app\excel\controller;

use Env;
use think\Db;
use think\Model;
use think\Controller;
use think\facade\Request;
use PHPExcel_IOFactory;
use Overtrue\Pinyin\Pinyin;

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
        $tname=Request::param('tname');
        $validate = new \app\excel\validate\Import;

        if (!$validate->scene('upload')->check(['tname'=>$tname,'template'=>$file])) {
            $this->error($validate->getError());
        }

        //将文件保存到public/uploads目录下面
        $info = $file->validate(['size'=>1048576,'ext'=>'xls,xlsx'])->move('./uploads');
        
        if ($info) {
            //获取上传到后台的文件名
            $fileName = $info->getSaveName();
            //获取文件路径
            $filePath = Env::get('root_path').'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$fileName;
            //获取文件后缀
            $suffix = $info->getExtension();
            //判断哪种类型
            if ($suffix=="xlsx") {
                $reader = \PHPExcel_IOFactory::createReader('Excel2007');
            } else {
                $reader = PHPExcel_IOFactory::createReader('Excel5');
            }
        } else {
            $this->error('文件过大或格式不正确导致上传失败-_-!');
        }
       
    
        $pinyin             = new Pinyin();
        $template           = new \app\excel\model\Templates;
        $user='lijian';
        $sum=Db::name('templates_sum')->find();
        $tabbr=$pinyin->abbr($tname);
        $template->tid      = 1000000000+$sum['count'];
        $template->tuser    = 'lijian';
        $template->tname    = $tname;
        $template->tfilename= $info->getInfo()['name'];
        $template->tfilepath= str_replace("\\", "/", $info->getPathName());
        $template->tabbr    = $tabbr;
        $res=$template->save();
        $tid=$template->id;
        // dump($tid);
        $data=readExcel($filePath);
        session('templateId', 1000000000+$sum['count']);
        session('templateData', $data);
        session('tabbr', $tabbr);
        return $this->success("模板文件上传成功！");
    }

    /**
     * 查看模板文件
     * @return void 读取结果
     */
    public function createTemplateSecond()
    {
        if (request()->isAjax()) {
            $data=session("optionList");
            // dump($data[0]['tid']);
            $pinyin = new Pinyin();

            $template=model('templates')
            ->where('tid', session('templateId'))
            ->find();
            //    dump($template);
            if ($template['status']=='1') {
                $this->error("模板已经初始化，不可更改");
            } else {
                $template->status = '1';
                $template->primaryKey = input('post.primaryKey');
                $template->ifUseXh = input('post.ifUseXH');
                $template->save();
            }
            $res2=model("TemplatesOption")->isUpdate(false)->saveAll($data);
            Db::name("templates_sum")->where('id', 1)->setInc('count');
            if ($res2) {
                $this->success("模板初始化成功", "excel/import/createtemplateThird");
            } else {
                $this->error("模板初始化失败");
            }
        }

        // $id=session('templateId');
        // $template=model("Templates")->with('getoption')->where(['id'=>$id])->find()->toArray();
        // $filePath=$template['tfilepath'];
        // $data=(readExcel($filePath));
        $data=session("templateData");
        end($data);
        $finalKey=key($data);

        $optionList=array();
        $tableField=array();
        $pinyin = new Pinyin();

        for ($col='A'; $col!=$finalKey;$col++) {
            $option='option_'.$col;
            for ($row=1; $row <= count($data[$col]); $row++) {
                $tmp=array();
                if ($row==1) {
                    $tmp['tid']=session('templateId');
                    $tmp['pid']='0';
                    $tmp['sid']=$option;
                    $tmp['type']='p';
                    $tmp['content']=$data[$col][$row];
                    $abbr=$pinyin->abbr($data[$col][1]);
                    $tableField[$option]=$data[$col][1];
                } else {
                    $tmp['tid']=session('templateId');
                    $tmp['pid']=$option;
                    $tmp['sid']=$option."_".$row;
                    $tmp['type']='c';
                    $tmp['content']=$data[$col][$row];
                }
                $optionList[]=$tmp;
            }
        }
        // dump($tableField);
        // dump($optionList);
        session('optionList', $optionList);
        $optionList=getOptionList($optionList, $pid='pid', $id='sid');
        // dump($optionList);
        return $this->fetch('createTemplateSecond', ['optionList'=>$optionList,'tname'=>session('tname'),'tableField'=>$tableField]);
    }


    
    public function createtemplatethird()
    {
        $shareUrl=url('excel/import/fill',['id'=>session("templateId")]);
        $this->assign("shareUrl",$shareUrl);
        return view();
    }
}
