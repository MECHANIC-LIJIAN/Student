<?php


namespace app\excel\controller;

use think\Controller;
use think\Db;
use Env;
use PHPExcel_IOFactory;
use think\Model;
use Overtrue\Pinyin\Pinyin;

class Import extends Controller
{
    public function index()
    {
        return view();
    }

    public function createtemplatefirst()
    {
        return view();
    }

    public function upload()
    {
        $tname=input('post.tname');
        $validate = new \app\excel\validate\Import;

        if (!$validate->scene('upload')->check(['tname'=>$tname])) {
            $this->error($validate->getError());
        }

        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $file = request()->file('tempalte');
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
        
        $tabbr=$pinyin->abbr($tname);
        $template->tuser    = 'lijian';
        $template->tname    = $tname;
        $template->tfilename= $info->getInfo()['name'];
        $template->tfilepath= str_replace("\\", "/", $info->getPathName());
        $template->tabbr    = $tabbr;
        $res=$template->save();
        $tid=$template->id;
        dump($tid);
        $data=(readExcel($filePath));
        session('templateId', $tid);
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
            $use=input('post.isUseXH');
            $primaryKey=input('post.primaryKey');
            $data=session("templateData");
            $tid=session("templateId");
            $tableField=session('templateField');
            // dump(array_keys($tableField));
            $pinyin = new Pinyin();
            
            $res=$this->createTable(array_keys($tableField), "stu_tempaltes_".session('tabbr'), $primaryKey);
            if ($res==1) {
                foreach ($data as $key => $value) {
                    $abbr=$pinyin->abbr($value[1]);
                    $res2=$this->dataToTemplate($tid, $value, $abbr);
                }
                if ($res2) {
                    $this->success("模板初始化成功", "excel/import/createtemplateFirst");
                }else {
                    $this->error($res);
                    
                }
            } else {
                $this->error($res);
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

        for ($i='A'; $i!=$finalKey;$i++) {
            for ($j=1; $j <= count($data[$i]); $j++) {
                $tmp=array();
                if ($j==1) {
                    $tmp['pid']='0';
                    $tmp['type']='p';
                    $tmp['id']=$i.'0';
                    $tmp['ocontent']=$data[$i][$j];
                    $tmp['idInTable']='10';
                    $abbr=$pinyin->abbr($data[$i][1]);
                    $tableField[$abbr]=$data[$i][1];
                } else {
                    $tmp['pid']=$i.'0';
                    $tmp['type']='c';
                    $tmp['id']=$j.$i;
                    $tmp['ocontent']=$data[$i][$j];
                    $tmp['idInTable']='10';
                }
                $optionList[]=$tmp;
            }
        }
        // dump($tableField);
        session('templateField', $tableField);
        $optionList=getOptionList($optionList, $pid='pid', $id='id');
        // dump($optionList);
        return $this->fetch('createTemplateSecond', ['optionList'=>$optionList,'tname'=>session('tname'),'tableField'=>$tableField]);
    }


    
    public function createTemplateThird()
    {
        if (request()->isAjax()) {
            $use=input('post.isUseXH');
            $primaryKey=input('post.primaryKey');
            $data=session("templateData");
            $tid=session("tempalteId");
            $tableField=session('templateField');
            // dump($tid);
            $pinyin = new Pinyin();
            foreach ($data as $key => $value) {
                $abbr=$pinyin->abbr($value[1]);
                $this->dataToTemplate($tid, $value, $abbr);
            }
            
            
            $res=$this->createTable($tableField, "stu_tempaltes_".session('tabbr'), $primaryKey);
            if ($res==1) {
                $this->success("模板初始化成功");
            } else {
                $this->error($res);
            }
        }
    }

    
    /**
     * 将模板存入数据库
     *
     * @param int $tid
     * @param array $data
     * @param string $abbr
     * @return void
     */
    public function dataToTemplate($tid, $data, $abbr)
    {
        // dump($data);
        // dump($pinyin->abbr($data[1]));
        $dateLen=count($data);
        $option           = new \app\excel\model\TemplatesOption;
        $option->opid     = '0';
        $option->tid      = $tid;
        $option->otype    = 'p';
        $option->ocontent = $data[1];
        $option->idInTable=$abbr;

        // dump($option);
        $res=$option->save();
        if ($dateLen>1) {
            // 获取自增ID
            $opid=$option->id;
            $data2=array();
            for ($i=2; $i <= $dateLen; $i++) {
                $line=[
                'tid'=>$tid,
                'otype'=>'c',
                'opid'=>$opid,
                'ocontent'=>$data[$i],
            ];
                $data2[$i-1]=$line;
            }
            $res=model("TemplatesOption")->isUpdate(false)->saveAll($data2);
        }
        if ($res) {
            return 1;
        }
    }

    
    /**
     * 创建数据表
     *
     * @param array $field
     * @param string $title
     * @param string $primaryKey
     * @return void
     */
    public function createTable($field, $title, $primaryKey)
    {
        $temp=" ";
        foreach ($field as $v) {
            $temp=$temp.$v." varchar(255) NOT NULL,";
        }
        $temp=$temp." ";
        
        $sql ="CREATE TABLE IF NOT EXISTS ".$title."(`id` int(8) unsigned NOT NULL AUTO_INCREMENT,"
        .$temp."PRIMARY KEY (`id`,`".$primaryKey."`))
        ENGINE=MyISAM
        DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
        CHECKSUM=0
        ROW_FORMAT=DYNAMIC
        DELAY_KEY_WRITE=0"
        ;
        // dump($sql);
        $res=Db::execute($sql);
        if ($res) {
            return 1;
        } else {
            return $this->error("模板初始化失败");
        }
    }
}

