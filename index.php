<?php
/**
 * Created by PhpStorm.
 * User: rudy
 * Date: 18-1-23
 * Time: 下午3:31
 */

/**
 * 创建连接
 */
$db_config = require 'database.php';

try {
    $con = new PDO("{$db_config['type']}:host={$db_config['host']};dbname=INFORMATION_SCHEMA", $db_config['username'], $db_config['password']);
    $con->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die('Error!: ' . $e->getMessage());
}


/**
 * 获取表数据和字段数据
 */
$tables = $con->query("SELECT TABLE_NAME,TABLE_COMMENT FROM TABLES WHERE TABLE_SCHEMA = '{$db_config['db']}'")->fetchAll(PDO::FETCH_ASSOC);
if (empty($tables)) {
    die('No Tables!');
}
$tables = array_column($tables, null, 'TABLE_NAME');

$fields = require 'fields.php';
$columns = implode(',', array_keys($fields)) . ',TABLE_NAME';
$result = $con->query("SELECT {$columns} FROM COLUMNS WHERE TABLE_SCHEMA = '{$db_config['db']}' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $v) {
    $tables[$v['TABLE_NAME']]['rows'][] = $v;
}


/**
 * 输出excel
 */
include "vendor/autoload.php";
$excel = new PHPExcel();
$excel->setActiveSheetIndex(0);//设置活动sheet
$act_sheet = $excel->getActiveSheet();//获取活动sheet

$pointer_row = 1;//行指针，初始化指向第一行
$cells = range(($ascii_a = ord('A')), $ascii_a + count($fields) - 1);//存在的列数组,如[A,B,C,D,E],此处存的是ascii的值,方便生成,后面会做转化
array_walk($cells, function (&$v) use ($act_sheet) {
    $v = chr($v);
    $act_sheet->getColumnDimension($v)->setWidth(20);//设置列宽度
});


foreach ($tables as $table) {

    /*
     * 表信息
     */
    $act_sheet->mergeCells(($table_cell = $cells[0] . $pointer_row) . ':' . end($cells) . $pointer_row)//合并他单元格
    ->setCellValue($table_cell, $table['TABLE_NAME'] . ($table['TABLE_COMMENT'] != '' ? "({$table['TABLE_COMMENT']})" : ''))//设置表名称和表注释
    ->getRowDimension($pointer_row)->setRowHeight(30);//设置行高
    $act_sheet->getStyle($table_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//垂直居中和水平居中
    $act_sheet->getStyle($table_cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FF2F4F4F');//设置背景颜色
    $act_sheet->getStyle($table_cell)->getFont()->setBold(true)->setSize(12);//设置文字
    ++$pointer_row;

    /*
     * 字段说明
     */
    $i = 0;
    foreach ($fields as $field => $alias) {
        $act_sheet->setCellValue(($cell = $cells[$i] . $pointer_row), $alias == '' ? $field : $alias)//设置字段说明
        ->getStyle($cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5DEB3');//设置颜色
        $i++;
    }
    ++$pointer_row;

    /*
     * 具体数据输出
     */
    foreach ($table['rows'] as $row) {
        $i = 0;
        foreach ($fields as $field => $alias) {
            $act_sheet->setCellValue($cells[$i] . $pointer_row, $row[$field]);//设置表字段对应的值
            $i++;
        }
        ++$pointer_row;
    }
    ++$pointer_row;
}

(new PHPExcel_Writer_Excel5($excel))->save("./{$db_config['db']}_tables.xls");