<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class FrontController extends FrontControllerCore
{
    public function pagination($total_products = null)
    {
        if (empty($this->context->custom_pagination)) {
            return parent::pagination($total_products);
        }
    }

    public function productSort()
    {
        if (empty($this->context->custom_sorting)) {
            return parent::productSort();
        }
    }
}
