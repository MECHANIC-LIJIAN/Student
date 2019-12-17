<?php


namespace app\excel\controller;

use think\Controller;

class Template extends Controller
{
    public function index($id)
    {
        $template=model("Templates")->with('getoption')->where(['id'=>$id])->find();
        $data=array(array());

        // dump($template['getoption'][0]);

        // $res=$this->getOptionList($template['getoption'], $pid='opid');

        $res=$this->getOptionList($template['getoption'], $pid='opid', $id='id');
        dump($res);
        return view();
    }



    /**
     * @param $data array  数据
     * @param $pid  string 父级元素的名称 如 parent_id
     * @param $id     string 子级元素的名称 如 comm_id
     * @param $p_id     int    父级元素的id 实际上传递元素的主键
     * @return array
     */
    public function getOptionList($data, $pid, $id, $p_id='0')
    {
        $tmp = array();
        foreach ($data as $key => $value) {
            if ($value[$pid] == $p_id) {
                // dump($value);
                $value['child'] =  $this->getOptionList($data, $pid, $id, $value[$id]);
                $tmp[] = $value;
            }
        }
        return $tmp;
    }
}
