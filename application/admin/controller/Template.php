<?php

namespace app\admin\controller;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
ini_set("memory_limit", "1024M");
class Template extends Base
{

    public function index()
    {
        $tId = input('id');

        $template = model('Templates')
            ->where(['tid' => $tId])
            ->field('id,tid,tname,ttype,primaryKey,myData,options')
            // ->json(['options'])
            ->find();

        #显示在页面的字段
        $options[] = [
            'checkbox' => true,
        ];
        $options[] = [
            'field' => 'id',
            'title' => '编号',
            'visible' => false, //这一列隐藏
        ];

        $template['options'] = json_decode($template['options'], true);

        $searchFields=[];
        #查询字段
        foreach ($template['options'] as $key => $value) {
            $tmp = [];
            $tmp['field'] = 'content.' . $key;
            $tmp['title'] = $value['title'];
            #内容过长的显示格式
            $tmp['formatter']="paramsMatter";
            if (!empty($template['primaryKey']) && $key == $template['primaryKey']) {
                $tmp['sortable'] = true;
            }
            if(!isset($value['options'])){
                $searchFields[]=$tmp;
            }
            $options[] = $tmp;
        }
        $options[] = [
            'field' => 'create_time',
            'title' => '首次提交时间',
            'sortable' => true,
        ];
        $options[] = [
            'field' => 'update_time',
            'title' => '最后提交时间',
            'sortable' => true,
        ];

        $options = json_encode($options);

        unset($template['options']);


        #构造分享链接
        $shareUrl = url('index/Template/readTemplate', ['id' => $tId], '', true);

        $this->assign([
            'template' => $template,
            'options' => $options,
            'shareUrl' => $shareUrl,
            'searchFields'=>$searchFields,
            'tId' => $tId,
        ]);
        return view();
    }

    /**
     * 删除表单数据
     * @return \think\response\View
     */
    public function del()
    {
        if (request()->isAjax()) {
            $ids = explode(',', input('ids'));
            sort($ids, SORT_NUMERIC);
            $result = model('TemplatesDatas')->destroy($ids);
            // $t = model("Templates")->where('id', $tId)->field('count')->find();
            // $t->count = $t->count - count($ids);
            // $result = $t->save();
            if ($result) {
                $this->success('数据删除成功');
            } else {
                $this->error("数据删除失败");
            }
        }
    }

    /**
     * 请求数据
     */

    public function dataList()
    {
        if (request()->isAjax()) {
            $params = input('post.');

            $tId = $params['tid'];
            $offset = $params['offset'];
            $limit = $params['limit'];
            $order = $params['order'];
            $ordername = $params['ordername'];
            $search = $params['search'];
            $searchField = $params['searchField'];

            #默认排序字段和规则
            $options = ['id,tid,content,create_time,update_time'];
            $orders = ['update_time' => 'desc'];

            #排序字段和规则
            if ($ordername == 'create_time' || $ordername == "update_time") {
                $orders = [$ordername => $order];
            } else {
                $tmp = explode(".", $ordername);
                if (count($tmp) == 2) {
                    array_push($options, "JSON_EXTRACT(content,'$." . $tmp[1] . "') as jsonOrder");
                    $orders = ['jsonOrder' => $order];
                }
            }

            #默认搜索条件
            $map[] = ['tid', '=', $tId];

            #模糊搜索
            if ($search != "") {
                if($searchField=="content.all"){
                    $map[] = ["content", 'like', "%$search%"];
                }else{
                    $map[] = ["content->".explode(".",$searchField)[1],'like',"%$search%"];
                }

            }
            # 计算页号
            $page = floor($offset / $limit) + 1;

            $list = model('TemplatesDatas')
                ->where($map)
                ->json(['content'])
                ->field($options)
                ->order($orders)
                ->page($page, $limit)
                // ->fetchSql()
                ->select();
            $count = model('TemplatesDatas')
                ->where(['tid' => $tId])
                ->where($map)
                ->count();
            $res = [
                'total' => $count,
                'rows' => $list,
            ];
            # 返回JSON数据
            return $res;
        }
    }


    /**
     * 数据导出
     *
     * @return void
     */
    public function export()
    {
        #默认搜索条件
        $map[] = ['tid', '=', input('post.id')];
        $options = ['id,tid,content,create_time,update_time'];
        $list = model('TemplatesDatas')
            ->where($map)
            // ->json(['content'])
            ->field($options)
            ->select();

        $outData = [];
        foreach ($list as $v) {
            array_push($outData, json_decode($v['content'], true));
        }
        $template = model('Templates')
            ->where('id', '=', input('post.id'))
            ->field('id,tname,options')
            ->json(['options'])
            ->find();
        $options = $template->options;
        $heads = [];
        $keys = [];
        foreach ($options as $key => $value) {
            array_push($heads, $value->title);
            array_push($keys, $key);
        }

        $filename = $template->tname;
        return $this->outdata($filename, $outData, $heads, $keys);

    }

/**
 * 通用导出方法。传入参数即可
 * @param unknown $filename 导出的excel文件名称，不包括后缀
 * @param unknown $rows 要导出的数据，数组
 * @param unknown $head 要导出数据的表头，数组
 * @param unknown $keys 要导出数据的键值对对应
 */
    public function outdata($filename, $rows = [], $head = [], $keys = [])
    {
        $count = count($head); //计算表头数量

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //设置样式,设置剧中，加边框，设置行高
        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '6184542'],
                ],
            ],
        ];
        $rows_count = count($rows);
        $sheet->getDefaultRowDimension()->setRowHeight(18); //设置默认行高。
        $sheet->getStyle('A1:' . strtoupper(chr($count + 65 - 1)) . strval($rows_count + 1))->applyFromArray($styleArray);
        $sheet->getStyle('A1:' . strtoupper(chr($count + 65 - 1)) . '1')->getFont()->setBold(false)->setName('Arial')->setSize(12)->applyFromArray($styleArray);

        // halt('A1:' . strtoupper(chr($count + 65 - 1)) . '1');
        //设置样式结束

        //写入表头信息
        for ($i = 65; $i < $count + 65; $i++) {
            //数字转字母从65开始，循环设置表头：
            $sheet->setCellValue(strtoupper(chr($i)) . '1', $head[$i - 65]);
        }

        //写入数据信息
        foreach ($rows as $key => $item) {
            //循环设置单元格：
            //$key+2,因为第一行是表头，所以写到表格时   从第二行开始写
            for ($i = 65; $i < $count + 65; $i++) {
                //数字转字母从65开始：
                $sheet->setCellValue(strtoupper(chr($i)) . ($key + 2), $item[$keys[$i - 65]]);

                $strLen=strlen($item[$keys[$i - 65]]);
                if($strLen>9||$strLen<21){
                    $sheet->setCellValueExplicit(strtoupper(chr($i)) . ($key + 2), $item[$keys[$i - 65]],\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
                
                $spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
            }

        }

        $filename = $filename . '.xlsx';

        header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //xlsx
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        ob_start(); //打开缓冲区
        $writer->save('php://output'); //这里就是乱码
        $xlsData = ob_get_contents();
        ob_end_clean(); // 清除缓冲

        $response = array(
            'code' => 1,
            'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($xlsData),
            'filename' => $filename,
            'msg' => '导出成功',
        );
        return $response;
    }
}
