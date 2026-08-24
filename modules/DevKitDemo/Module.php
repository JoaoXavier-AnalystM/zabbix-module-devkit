<?php

namespace Modules\DevKitDemo;

use Zabbix\Core\CModule,
    APP,
    CMenuItem;

class Module extends CModule
{
    public function init(): void
    {
        APP::Component()->get('menu.main')
            ->add(
                (new CMenuItem(_('Zabbix Status')))
                    ->setAction('devkitdemo.view')
            );
    }
}
