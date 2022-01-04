<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Search extends SearchCore
{
    public static function find(
        $id_lang,
        $expr,
        $page_number = 1,
        $page_size = 1,
        $order_by = 'position',
        $order_way = 'desc',
        $ajax = false,
        $use_cookie = true,
        Context $context = null
    ) {
        if (!$context) {
            $context = Context::getContext();
        }
        if (isset($context->filtered_result) && $context->filtered_result['controller'] == 'search') {
            $ret = array(
                'total' => $context->filtered_result['total'],
                'result' => $context->filtered_result['products']
            );
            return $ret;
        } else {
            return parent::find(
                $id_lang,
                $expr,
                $page_number,
                $page_size,
                $order_by,
                $order_way,
                $ajax,
                $use_cookie,
                $context
            );
        }
    }
}
