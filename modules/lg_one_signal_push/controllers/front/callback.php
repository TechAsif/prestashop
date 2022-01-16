<?php

class LgOneSignalCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $this->module->sendCallback();
        die;
    }
}
