<?php

namespace Modules\DevKitDemo\Actions;

use CController;
use CControllerResponseData;
use Modules\DevKitDemo\Services\ZabbixServerStatusService;

require_once dirname(__DIR__) . '/include/Services/ZabbixServerStatusService.php';

class DevKitDemo extends CController
{
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
        $status = (new ZabbixServerStatusService())->getStatus();

        $this->setResponse(new CControllerResponseData([
            'title' => _('Zabbix server status'),
            'status' => $status
        ]));
    }
}
