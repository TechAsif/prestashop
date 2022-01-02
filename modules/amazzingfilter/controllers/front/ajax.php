<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AmazzingFilterAjaxModuleFrontController extends ModuleFrontControllerCore
{
    public function initContent()
    {
        $this->module->defineSettings();
        if ($params = $this->module->parseStr(Tools::getValue('params'))) {
            $this->module->ajaxGetFilteredProducts($params);
        } elseif (Tools::getValue('action') == 'SaveMyFilters') {
            $this->module->ajaxSaveCustomerFilters();
        }
        /*
        } else if (Tools::getValue('action') == 'getRedirectLink') {
            $link_params = Tools::getValue('link_params');
            $id_category = $link_params['id_category'];
            unset($link_params['id_category']);
            $link = new Link();
            $url = $link->getCategoryLink($id_category);
            $params = strpos($url, '?') ? '&' : '?';
            $params .= http_build_query($link_params);
            $ret = array('url' => $url.$params);
            exit(Tools::jsonEncode($ret));
        }
        */
    }
}
