<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AmazzingFilterMyFiltersModuleFrontController extends ModuleFrontControllerCore
{
    public function init()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        $this->module->defineSettings();
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();
        $this->context = Context::getContext();
        $adjustable_filters = $this->module->getAdjustableCustomerFilters();

        if (!$this->context->customer->id || !$adjustable_filters) {
            Tools::redirect('my-account');
        }

        $all_available_filters = $this->module->getAvailableFilters(false);
        $customer_filters = $this->module->getCustomerFilters($this->context->customer->id);

        $filters = array();
        foreach ($adjustable_filters as $f) {
            if (empty($all_available_filters[$f])) {
                continue;
            }
            $filters[$f] = array(
                'name' =>  $all_available_filters[$f]['name'],
                'submit_name' =>  'filters['.$f.'][]',
                'first_char' => Tools::substr($f, 0, 1),
                'id_group' => Tools::substr($f, 1),
            );
            if ($filters[$f]['first_char'] == 'c') {
                $this->module->c_groups = $this->module->formatIDs($this->context->customer->getGroups(), true);
                $filters[$f]['id_parent'] = $filters[$f]['id_group'] ?: $this->context->shop->getCategory();
                $filters[$f]['nesting_lvl'] = 0;
                $filters[$f]['values'] = $this->module->prepareTreeValues(
                    $this->module->getRawFilterValues($filters[$f]),
                    $filters[$f]['id_parent']
                );
            } else {
                $filters[$f]['values'] = $this->module->getRawFilterValues($filters[$f]);
            }
        }

        $this->context->smarty->assign(array(
            'filters' => $filters,
            'customer_filters' => $customer_filters,
            'layout_classes' => $this->module->getLayoutClasses(),
        ));
        $this->setCurrentTemplate('my-filters.tpl');
    }

    public function setCurrentTemplate($tpl_name, $settings = array())
    {
        if ($this->module->is_17) {
            $this->context->smarty->assign(array(
                'html' => $this->displayTemplate($tpl_name),
            ));
            $page = 'module-'.$this->module->name.'-myfilters';
            $this->context->controller->php_self = $page;
            if (!empty($settings['display_column_left']) && !empty($settings['display_column_right'])) {
                $layout = 'both-columns';
            } elseif (!empty($settings['display_column_left'])) {
                $layout = 'left-column';
            } elseif (!empty($settings['display_column_right'])) {
                $layout = 'right-column';
            } else {
                $layout = 'full-width';
            }
            $this->context->shop->theme->setPageLayouts(array($page => 'layout-'.$layout));
            $this->setTemplate('module:amazzingfilter/views/templates/front/content-17.tpl');
        } else {
            $this->setTemplate($tpl_name);
        }
    }

    public function displayTemplate($tpl_name)
    {
        $local_path = _PS_MODULE_DIR_.$this->module->name.'/'.$this->module->name.'.php';
        return $this->module->display($local_path, 'views/templates/front/'.$tpl_name);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Filtering preferences', 'myfilters'),
            'url' => $this->context->link->getModuleLink($this->module->name, 'myfilters'),
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->module->addJS('my-filters.js');
        $this->module->addCSS('my-filters.css');
        $this->module->loadIconFontIfRequired();
        $params = array('action' => 'SaveMyFilters');
        Media::addJsDef(array(
            'af_ajax_path' => $this->context->link->getModuleLink($this->module->name, 'ajax', $params),
            'savedTxt' => $this->module->saved_txt,
            'af_classes' => $this->module->getLayoutClasses(),
        ));
    }
}
