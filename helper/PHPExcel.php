<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/21
 * Time: 10:22
 */

namespace Helpers;


class PHPExcel
{

    const EXCEL_FORMAT = '.xlsx';

    public function __construct()
    {
        include_once(BASE_PATH.'/vendor/phpoffice/phpexcel/Classes/PHPExcel.php');
        include_once (BASE_PATH.'/vendor/phpoffice/phpexcel/Classes/PHPExcel/Writer/Excel2007.php');
    }

    /**
     * 导出
     *
     * @param $fileName     --文件名称
     * @param $sheetArr  --sheet标题
     * @param bool $isDown
     * @author lp
     * @throws
     */
    public function exportExcel($fileName,$sheetArr,$isDown = true)
    {
        $excel = new \PHPExcel();
        $objWriter = new \PHPExcel_Writer_Excel2007($excel);
        $arrange = range('A','Z');
        if(is_array($sheetArr)){
            foreach($sheetArr as $key => $value){
                $arr_num     = count($value['dataList']);   //行数
                $field_count = count($value['headerArr']);  //列数
                $excel->createSheet($key);
                $Sheet = $excel->setActiveSheetIndex($key);
                //大标题
                if (is_array($value['titleArr'])) {
                    foreach ($value['titleArr'] as $t => $title) {
                        $Sheet->mergeCells('A' . ($t + 1) . ':' . $arrange[$field_count - 1] . ($t + 1));
                        $Sheet->getStyle('A' . ($t + 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $Sheet->getStyle('A' . ($t + 1))->getFont()->setBold(true);  //加粗
                        $Sheet->setCellValue('A' . ($t + 1), $title);
                    }
                }
                //设置表头
                $start = count($value['titleArr']) + 1;
                if (is_array($value['headerArr'])) foreach ($value['headerArr'] as $k => $v) {
                    $Sheet->getStyle($arrange[$k] . $start)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $Sheet->getStyle($arrange[$k] . $start)->getFont()->setBold(true);  //加粗
                    $v['width'] && $Sheet->getColumnDimension($arrange[$k])->setWidth($v['width']);
                    $Sheet->setCellValue($arrange[$k] . $start, $v['name']);
                }
                $start += 1;
                for ($i = 0; $i < $arr_num; $i++) {
                    for ($j = 0; $j <= $field_count; $j++) {
                        $Sheet->getStyle($arrange[$j] . $start)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        @$Sheet->setCellValue($arrange[$j] . ($start), $value['dataList'][$i][$value['headerArr'][$j]['field']]);
                    }
                    $start++;
                }
                //扩展数据
                if($extendData = $value['extendData']){
                    $start = $arr_num + count($value['titleArr']) + 2;      //扩展数据开始行
                    $extendHeaderLen = count($extendData['header']) - 1;    //扩展数据表头长度
                    if($extendData['title']){
                        $Sheet->mergeCells('A' . ($start) . ':' . $arrange[$extendHeaderLen] . ($start));
                        $Sheet->mergeCells('A' . ($start + 1) . ':' . $arrange[$extendHeaderLen] . ($start + 1));
                        $Sheet->getStyle('A' . ($start + 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $Sheet->getStyle('A' . ($start + 1))->getFont()->setBold(true);  //加粗
                        $Sheet->setCellValue('A' . ($start + 1), $extendData['title']);
                    }

                    if($extendData['header']){
                        $start = $arr_num + 6;
                        if (is_array($extendData['header'])) foreach ($extendData['header'] as $k => $v) {
                            $Sheet->getStyle($arrange[$k] . $start)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $Sheet->getStyle($arrange[$k] . $start)->getFont()->setBold(true);  //加粗
                            $v['width'] && $Sheet->getColumnDimension($arrange[$k])->setWidth($v['width']);
                            $Sheet->setCellValue($arrange[$k] . $start, $v['name']);
                        }
                    }
                    if($extendData['dataList']){
                        $start = $arr_num + 7;
                        for ($i = 0; $i < $arr_num; $i++) {
                            for ($j = 0; $j <= $field_count; $j++) {
                                $Sheet->getStyle($arrange[$j] . $start)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $Sheet->setCellValue($arrange[$j] . ($start), $extendData['dataList'][$i][$extendData['header'][$j]['field']]);
                            }
                            $start++;
                        }
                    }
                }
                $excel->getActiveSheet()->setTitle($value['groupName']);
            }
        }
        if($isDown){
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header('Content-Disposition:attachment;filename='.$fileName.self::EXCEL_FORMAT);
            header("Content-Transfer-Encoding:binary");
            //导出数据
            $objWriter->save('php://output');
            exit;
        }
    }



}