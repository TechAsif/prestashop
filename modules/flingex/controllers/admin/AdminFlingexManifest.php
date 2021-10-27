<?php
/**
 * DHL Deutschepost
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2021 silbersaiten
 * @license   See joined file licence.txt
 * @category  Module
 * @support   silbersaiten <support@silbersaiten.de>
 * @version   1.0.7
 * @link      http://www.silbersaiten.de
 */

class AdminFlingexManifestController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = '';
        $this->bootstrap = true;
        $this->show_toolbar = false;
        $this->multishop_context = Shop::CONTEXT_SHOP;
        $this->context = Context::getContext();

        parent::__construct();

        $this->display = 'manifest';
    }

    public function initContent()
    {
        parent::initContent();
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->displayInformation($this->l('You can only display the page in a shop context.'));
        } else {
            if ($this->display == 'manifest') {
                $this->content .= $this->renderManifest();
            }
        }
    }

    public function initProcess()
    {
        parent::initProcess();
        if (Tools::getIsset('manifest'.$this->table)) {
            $this->display = 'manifest';
            $this->action = 'manifest';
        }
    }

    public function postProcess()
    {
        parent::postProcess();    
    }

    public function renderManifest()
    {
        $flingex_orders = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'flingex_order po 
            INNER JOIN '._DB_PREFIX_.'orders odr ON (po.id_order=odr.id_order)
            ORDER BY \'po.date_add\' DESC LIMIT 20
            '
            // INNER JOIN '._DB_PREFIX_.'order_detail od ON (po.id_order=od.id_order)
            // LEFT JOIN '._DB_PREFIX_.'flingex_order_tracking pot ON (po.id_flingex_order=pot.id_flingex_order)group by po.reference'
        );
        // var_dump($flingex_orders);

        foreach ($flingex_orders as $key=>$order){
            $_SQL = 'SELECT * FROM '._DB_PREFIX_.'flingex_order_tracking where id_order ='.$order['id_order'];
            $flingex_orders[$key]['tracking_data'] = Db::getInstance()->executeS($_SQL);
            $ORDER_DETAILS_SQL = 'SELECT * FROM '._DB_PREFIX_.'order_detail where id_order ='.$order['id_order'];
            $flingex_orders[$key]['products'] = Db::getInstance()->executeS($ORDER_DETAILS_SQL);
        }

        $this->context->smarty->assign([
            'manifestDate' => Tools::getValue('manifestDate', date('Y-m-d')),
            'orders' => $flingex_orders,
        ]);
        $this->setTemplate('manifest.tpl');
    }
}
