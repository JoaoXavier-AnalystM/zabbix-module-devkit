<?php

namespace Modules\DevKitDemo\Actions;

use CController;
use CControllerResponseData;

class DevKitDemo extends CController
{
    protected function init(): void
    {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool
    {
        return true;
    }

    protected function checkPermissions(): bool
    {
        return true;
    }

    protected function doAction(): void
    {
        $this->setResponse(new CControllerResponseData([
            'title' => 'Zabbix Module DevKit'
        ]));
    }
}
