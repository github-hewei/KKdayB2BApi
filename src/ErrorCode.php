<?php // CODE BY HW
namespace KKdayB2BApi;

class ErrorCode
{
    /**
     * 回传正确
     */
    const SUCCESS = '00';

    /**
     * 未知错误
     */
    const UNKNOWN = '01';

    /**
     * 输入参数错误
     */
    const MISSING_FIELD = '02';

    /**
     * 查无商品资讯
     */
    const CAN_NOT_FIND_PROD = '03';
}
