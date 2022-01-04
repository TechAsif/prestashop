<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Bo extends AmazzingFilter
{
    public function addConfigMedia()
    {
        $this->addJquery();
        $this->context->controller->addJqueryUI('ui.sortable');
        $this->context->controller->css_files[$this->_path.'views/css/back.css?v='.$this->version] = 'all';
        if ($this->is_17) {
            $this->context->controller->css_files[$this->_path.'views/css/back-17.css?'.$this->version] = 'all';
        }
        $this->context->controller->js_files[] = $this->_path.'views/js/back.js?v='.$this->version;
        if (!empty($this->sp)) {
            $sp_path = _MODULE_DIR_.$this->sp->name.'/';
            $this->context->controller->js_files[] = $sp_path.'views/js/back.js?v='.$this->sp->version;
            $this->context->controller->css_files[$sp_path.'views/css/back.css?v='.$this->sp->version] = 'all';
        }
        // mce
        $this->context->controller->addJS(__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js');
        $this->context->controller->addJS(__PS_BASE_URI__.'js/admin/tinymce.inc.js');
    }

    public function addJquery()
    {
        $this->defineSettings();
        if (empty($this->context->jqueryAdded)) {
            version_compare(_PS_VERSION_, '1.7.6.0', '>=') ? $this->context->controller->setMedia() :
            $this->context->controller->addJquery();
            $this->context->jqueryAdded = 1;
        }
    }

    public function setWarningsIfRequired()
    {
        if ($this->active) {
            foreach (array('blocklayered', 'ps_facetedsearch') as $module_name) {
                if (Module::isEnabled($module_name)) {
                    $txt = $this->l('Please, disable module %s in order to avoid possible interference', 'bo');
                    $this->context->controller->warnings[] = sprintf($txt, $module_name);
                }
            }
            if (Module::isEnabled('iqitthemeeditor') && Configuration::get('iqitthemeed_pl_infinity')) {
                $iqit = 'IqitThemeEditor settings';
                if ($this->is_17) {
                    $link = $this->context->link->getAdminLink('AdminIqitThemeEditor');
                    $iqit = '<a href="'.$link.'" target="_blank">'.$iqit.'</a>';
                }
                $txt = 'Set "Infinity scroll - NO" in '.$iqit;
                if ($this->settings['general']['p_type'] < 2) {
                    $txt .= ', and then select "Pagination Type - Infinite scroll" in General settings below ↓';
                }
                $this->context->controller->warnings[] = $txt;
            }
        }
    }

    public function getFilesUpdadeWarnings()
    {
        $warnings = $customizable_layout_files = array();
        $locations = array(
            '/css/' => 'css',
            '/js/'  => 'js',
            '/templates/admin/' => 'tpl',
            '/templates/hook/' => 'tpl',
            '/templates/front/' => 'tpl',
        );
        foreach ($locations as $loc => $ext) {
            $loc = 'views'.$loc;
            $files = glob($this->local_path.$loc.'*.'.$ext);
            foreach ($files as $file) {
                $customizable_layout_files[] = '/'.$loc.basename($file);
            }
        }
        foreach ($customizable_layout_files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($file == '/views/css/custom.css' || $file == '/views/js/custom.js') {
                continue;
            }
            if ($this->is_17) {
                $customized_file_path = _PS_THEME_DIR_.'modules/'.$this->name.$file;
            } else {
                $customized_file_path = _PS_THEME_DIR_.($ext != 'tpl' ? $ext.'/' : '').'modules/'.$this->name.$file;
            }
            if (file_exists($customized_file_path)) {
                $original_file_path = $this->local_path.$file;
                $original_rows = file($original_file_path);
                $original_identifier = trim(array_pop($original_rows));
                $customized_rows = file($customized_file_path);
                $customized_identifier = trim(array_pop($customized_rows));
                if ($original_identifier != $customized_identifier) {
                    $warnings[$file] = $original_identifier;
                }
            }
        }
        return $warnings;
    }
}
