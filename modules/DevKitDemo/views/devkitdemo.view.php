<?php

$status = $data['status'];
$host = $status['host'];
$availabilityClass = 'status-' . $status['availability'];
$resources = $status['resources'];
$severityNames = [
    _('Not classified'),
    _('Information'),
    _('Warning'),
    _('Average'),
    _('High'),
    _('Disaster')
];

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
    $summaryTable = (new CTableInfo())->setHeader([_('Status'), _('Value')]);
    $summaryTable
        ->addRow([_('Interface'), (new CSpan($status['availability_text']))->addClass($availabilityClass)])
        ->addRow([_('CPU usage'), $resources['cpu_usage'] ?? _('No data')])
        ->addRow([_('CPU total'), $resources['cpu_total'] ?? _('No data')])
        ->addRow([_('Memory usage'), $resources['memory_usage'] ?? _('No data')])
        ->addRow([_('Memory total'), $resources['memory_total'] ?? _('No data')])
        ->addRow([_('Active problems'), array_sum($status['problem_counts'])]);

    $content->addItem((new CTag('h3', true, _('Zabbix server overview'))));
    $content->addItem($summaryTable);

    $content->addItem(
        (new CTableInfo())
            ->setHeader([_('Property'), _('Value')])
            ->addRow([_('Host'), $host['name']])
            ->addRow([_('Technical name'), $host['technical_name']])
            ->addRow([_('Host status'), $host['status'] === '0' ? _('Monitored') : _('Not monitored')])
            ->addRow([_('Interface availability'), (new CSpan($status['availability_text']))->addClass($availabilityClass)])
            ->addRow([_('Active problems'), array_sum($status['problem_counts'])])
    );

    $problemTable = (new CTableInfo())->setHeader([_('Severity'), _('Count')]);
    foreach ($status['problem_counts'] as $severity => $count) {
        if ($count > 0) {
            $problemTable->addRow([$severityNames[$severity], $count]);
        }
    }

    if (!$status['problems']) {
        $problemTable->addRow([_('No active problems.'), 0]);
    }

    $content->addItem((new CTag('h4', true, _('Active problems'))));
    $content->addItem($problemTable);

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
