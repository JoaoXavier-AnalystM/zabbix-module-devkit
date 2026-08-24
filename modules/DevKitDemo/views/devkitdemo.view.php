<?php

$status = $data['status'];
$host = $status['host'];
$availabilityClass = 'status-' . $status['availability'];

$content = new CDiv();

if ($status['error'] !== null) {
    $content->addItem(
        (new CTag('div', true, $status['error']))
            ->addClass(ZBX_STYLE_MSG_BAD)
    );
}

if ($host === null) {
    $content->addItem((new CDiv())->addItem(_('No Zabbix server data is available.')));
}
else {
    $content->addItem(
        (new CTableInfo())
            ->setHeader([_('Property'), _('Value')])
            ->addRow([_('Host'), $host['name']])
            ->addRow([_('Technical name'), $host['technical_name']])
            ->addRow([_('Host status'), $host['status'] === '0' ? _('Monitored') : _('Not monitored')])
            ->addRow([_('Interface availability'), (new CSpan($status['availability_text']))->addClass($availabilityClass)])
    );

    $itemsTable = (new CTableInfo())->setHeader([_('Process metric'), _('Last value'), _('Last update')]);

    foreach ($status['process_items'] as $item) {
        $value = $item['value'] !== '' ? $item['value'] . $item['units'] : _('No value');
        $updated = $item['clock'] > 0 ? date(_('Y-m-d H:i:s'), $item['clock']) : _('Never');
        $itemsTable->addRow([$item['name'], $value, $updated]);
    }

    if (!$status['process_items']) {
        $itemsTable->addRow([_('No process metrics found.'), '', '']);
    }

    $content->addItem((new CTag('h4', true, _('Zabbix process health'))));
    $content->addItem($itemsTable);
}

(new CWidget())
    ->setTitle($data['title'])
    ->addItem($content)
    ->show();
