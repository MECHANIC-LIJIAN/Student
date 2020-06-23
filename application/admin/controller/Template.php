<?php

namespace app\admin\controller;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Template extends Base
{

    public function index()
    {
        $tId = input('id');

        $template = model('Templates')
            ->where(['tid' => $tId])
            ->field('id,tid,tname,primaryKey,myData,options')
            // ->json(['options'])
            ->find();

        #显示在页面的字段
        $fields[] = [
            'checkbox' => true,
        ];
        $fields[] = [
            'field' => 'id',
            'title' => '编号',
            'visible' => false, //这一列隐藏
        ];

        $template['options'] = json_decode($template['options'], true);

        #查询字段
        foreach ($template['options'] as $key => $value) {
            $tmp = [];
            $tmp['field'] = 'content.' . $key;
            $tmp['title'] = $value['title'];
            if (!empty($template['primaryKey']) && $key == $template['primaryKey']) {
                $tmp['sortable'] = true;
            }
            $fields[] = $tmp;
        }

        $fields[] = [
            'field' => 'create_time',
            'title' => '首次提交时间',
            'sortable' => true,
        ];
        $fields[] = [
            'field' => 'update_time',
            'title' => '最后提交时间',
            'sortable' => true,
        ];

        $fields = json_encode($fields);

        unset($template['options']);

        session('tInfo', $template);

        #构造分享链接
        $shareUrl = url('index/Template/readTemplate', ['id' => $tId], '', true);

        $this->assign([
            'template' => $template,
            'fields' => $fields,
            'shareUrl' => $shareUrl,
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

            $tId = session('tInfo.id');
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
            $template = session('tInfo');
            $tId = $template['id'];
            $primaryKey = $template['primaryKey'];
            $offset = input('post.offset');
            $limit = input('post.limit');
            $order = input('post.order');
            $ordername = input('post.ordername');
            $search = input('post.search');

            #默认排序字段和规则
            $fields = ['id,tid,content,create_time,update_time'];
            $orders = ['update_time' => 'desc'];

            #排序字段和规则
            if ($ordername) {
                if ($ordername == 'content.' . $primaryKey) {
                    array_push($fields, "JSON_EXTRACT(content,'$.$primaryKey') as jsonOrder");
                    $orders = ['jsonOrder' => $order];
                } else {
                    $orders = [$ordername => $order];
                }
            }

            #默认搜索条件
            $map[] = ['tid', '=', $tId];
            #模糊搜索
            if ($search != "") {
                $map[] = ["content", 'like', "%$search%"];
            }

            #判断是否为导出
            if ($limit != null) {
                # 计算页号
                $page = floor($offset / $limit) + 1;
                $list = model('TemplatesDatas')
                    ->where($map)
                    ->json(['content'])
                    ->field($fields)
                    ->order($orders)
                    ->page($page, $limit)
                    ->withAttr([
                        'create_time' => function ($value) {
                            return date("Y-m-d H:i", $value);
                        },
                        'update_time' => function ($value) {
                            return date("Y-m-d H:i", $value);
                        },
                    ])
                    ->select();
            } else {
                $list = model('TemplatesDatas')
                    ->where($map)
                    ->json(['content'])
                    ->field($fields)
                    ->withAttr([
                        'create_time' => function ($value) {
                            return date("Y-m-d H:i", $value);
                        },
                        'update_time' => function ($value) {
                            return date("Y-m-d H:i", $value);
                        },
                    ])
                    ->select();
            }

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

    public function export()
    {
        #默认搜索条件
        $map[] = ['tid', '=', input('post.id')];
        $fields = ['id,tid,content,create_time,update_time'];
        $list = model('TemplatesDatas')
            ->where($map)
            // ->json(['content'])
            ->field($fields)
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
        $sheet->getStyle('A1:' . strtoupper(chr($count + 65 - 1)) . '1')->getFont()->setBold(true)->setName('Arial')->setSize(12)->applyFromArray($styleArray);
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
                $spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
            }

        }

        $filename=$filename . '.xlsx';
        
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
            'filename'=>$filename,
            'msg' => '导出成功',
        );
        return $response;
    }
}
