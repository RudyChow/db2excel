<?php
/**
 * Created by PhpStorm.
 * User: rudy
 * Date: 18-1-23
 * Time: 下午4:07
 * desc: 对应导出的字段，如不需要则注释掉相关的即可，如果需要也可往数组添加字段，只要数据库中存在的字段即可
 *       key为数据库中information_schema中columns的字段名称，value为自定义的alias
 */
return [
    'COLUMN_NAME' => '列名',
    'COLUMN_TYPE' => '数据类型',
    'DATA_TYPE' => '字段类型',
//    'CHARACTER_MAXIMUM_LENGTH' => '长度',
    'IS_NULLABLE' => '是否为空',
    'COLUMN_DEFAULT' => '默认值',
    'COLUMN_COMMENT' => '备注'
];