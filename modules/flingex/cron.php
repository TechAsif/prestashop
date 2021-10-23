<?php
// http://localhost/ps174/modules/flingex/cron.php?return_message=1&run=1

include dirname(__FILE__).'/../../config/config.inc.php';
include dirname(__FILE__).'/flingex.php';


if (!Module::isInstalled('flingex')) {
    die('Flingex Module is not Installed');
}
if (Tools::getValue('ajax')) {
    // Case of nothing to do but showing a message (1)
    if (Tools::getValue('return_message') !== false) {
        echo '1';
        die();
    }

    if (Tools::usingSecureMode()) {
        $domain = Tools::getShopDomainSsl(true);
    } else {
        $domain = Tools::getShopDomain(true);
    }
    // Return a content without waiting the end of index execution
    header('Location: '.$domain.__PS_BASE_URI__.'modules/flingex/cron.php?token='.Tools::getValue('token').'&return_message='.(int) Tools::getValue('cursor'));
    flush();
}

if (Tools::getValue('run')) {
    $flingsexAPI = new Flingex();
    echo $flingsexAPI->flingex_api->cronProcess((int) Tools::getValue('run'), true);
}
