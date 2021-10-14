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

class AdminDhldpManifestController extends ModuleAdminController
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
        if (Tools::getIsset('manifest'.$this->table)) {
            if (Tools::isSubmit('getManifest')) {
                if (Tools::getValue('manifestDate', '') == '') {
                    $this->errors[] = Tools::displayError('Please select manifest date. Manifest date is empty.');
                } else {
                    $this->module->dhldp_api->setApiVersion(Configuration::get('DHLDP_DHL_API_VERSION', null, null, Context::getContext()->shop->id));
                    $response = $this->module->dhldp_api->callDhlApi(
                        ($this->module->dhldp_api->getMajorApiVersion() == 1)?'getManifestDD':'getManifest',
                        array(
                            'manifestDate' => Tools::getValue('manifestDate')
                        ),
                        Context::getContext()->shop->id
                    );
                    if (is_object($response) && (isset($response->manifestData) || isset($response->ManifestPDFData) || isset($response->ManifestPdfData))) {
                        $file_path = $this->module->getLocalPath().'/pdfs/manifest'.str_replace('-', '', preg_replace('#[^a-zA-Z0-9\_\-]#','',Tools::getValue('manifestDate'))).'.pdf';
                        $f = fopen($file_path, 'wb');
                        if (isset($response->manifestData)) {
                            $data = $response->manifestData;
                        } elseif (isset($response->ManifestPDFData)) {
                            $data = $response->ManifestPDFData;
                        } else {
                            $data = $response->ManifestPdfData;
                        }

                        if (strstr($data, '%PDF') === false) {
                            $data = base64_decode($data);
                        }
                        fwrite($f, $data);
                        fclose($f);
                        if (file_exists($file_path)) {
                            //ob_clean();
                            header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
                            header('Pragma: public');
                            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                            header('Content-Type: application/pdf');
                            $afile_path = explode('.', basename($file_path));
                            $afile_path[0] .= '_'.uniqid();
                            header('Content-Disposition: attachment; filename="'.implode('.', $afile_path).'"');
                            header('Content-Transfer-Encoding: binary');
                            //echo file_get_contents($file_path);
                            header('Content-Length: ' . filesize($file_path));
                            readfile($file_path);
                            exit;
                        }
                    } else {
                        if (count($this->module->dhldp_api->errors)) {
                            foreach ($this->module->dhldp_api->errors as $err) {
                                $this->errors[] = Tools::displayError($err);
                            }
                        }
                    }
                }
            }
        } else {
            parent::postProcess();
        }
    }

    public function renderManifest()
    {
        $this->context->smarty->assign('manifestDate', Tools::getValue('manifestDate', date('Y-m-d')));
        $this->setTemplate('manifest.tpl');
    }
}
