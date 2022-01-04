<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AdminProductsController extends AdminProductsControllerCore
{
    public function processAdd()
    {
        $ret = parent::processAdd();
        Hook::exec('actionIndexProduct', array('product' => $this->object->id));
        return $ret;
    }

    public function processUpdate()
    {
        $ret = parent::processUpdate();
        Hook::exec('actionIndexProduct', array('product' => $this->object->id));
        return $ret;
    }
}
