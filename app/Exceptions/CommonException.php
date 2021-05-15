<?php

namespace App\Exceptions;

use Exception;

class CommonException extends Exception
{
    //
    static public function msg(int $code, $data): string
    {
        $retValToMsg = array(
            0 => '成功',
            1 => '优惠劵已存在',
            2 => '优惠劵更新失败',
            3 => '优惠劵查找失败',
            4 => '优惠劵获取失败',
            5 => '优惠劵使用失败',
            6 => '优惠劵已用完',
            7 => '图片保存失败',
            8 => '密钥错误',
            9 => '保存商家失败',
            10 => '更新商家失败',
            11 =>'优惠劵添加失败',
            65533 => '操作失败',
            65534 => '未知错误',
            65535 => '缺失参数'
        );

        return json_encode(
            ["code" => $code, "message" => $retValToMsg[$code], "data" => $data],
            JSON_UNESCAPED_UNICODE);
    }

}
