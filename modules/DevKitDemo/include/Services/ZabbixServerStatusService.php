<?php

namespace Modules\DevKitDemo\Services;

use API;

class ZabbixServerStatusService
{
    private const HOST_NAME = 'Zabbix server';

    public function getStatus(): array
    {
        $status = [
            'host' => null,
            'availability' => 'unknown',
            'availability_text' => _('Unknown'),
            'process_items' => [],
            'problems' => [],
            'problem_counts' => array_fill(0, 6, 0),
            'error' => null
        ];

        try {
            $hosts = API::Host()->get([
                'output' => ['hostid', 'host', 'name', 'status'],
                'selectInterfaces' => ['interfaceid', 'type', 'main', 'available', 'error'],
                'filter' => ['name' => [self::HOST_NAME]],
                'limit' => 1
            ]);

            if (!$hosts) {
                $status['error'] = _('The Zabbix server host was not found.');
                return $status;
            }

            $host = $hosts[0];
            $status['host'] = [
                'hostid' => $host['hostid'],
                'name' => $host['name'],
                'technical_name' => $host['host'],
                'status' => $host['status']
            ];
            $status['availability'] = $this->getAvailability($host['interfaces'] ?? []);
            $status['availability_text'] = $this->getAvailabilityText($status['availability']);
            $status['process_items'] = $this->getProcessItems($host['hostid']);
            $status['problems'] = $this->getProblems($host['hostid']);
            foreach ($status['problems'] as $problem) {
                $status['problem_counts'][$problem['severity']]++;
            }
        }
        catch (\Throwable $exception) {
            $status['error'] = _('Unable to load Zabbix server status.');
        }

        return $status;
    }

    private function getProblems(string $hostId): array
    {
        $problems = API::Problem()->get([
            'output' => ['eventid', 'name', 'clock', 'severity'],
            'hostids' => [$hostId],
            'source' => 0,
            'object' => 0,
            'sortfield' => 'clock',
            'sortorder' => 'DESC',
            'limit' => 50
        ]);

        return array_map(static function (array $problem): array {
            return [
                'eventid' => $problem['eventid'],
                'name' => $problem['name'],
                'clock' => (int) $problem['clock'],
                'severity' => (int) $problem['severity']
            ];
        }, $problems);
    }

    private function getProcessItems(string $hostId): array
    {
        $items = API::Item()->get([
            'hostids' => [$hostId],
            'output' => ['itemid', 'name', 'key_', 'lastvalue', 'lastclock', 'units', 'state'],
            'search' => ['key_' => 'zabbix['],
            'searchByAny' => false,
            'sortfield' => 'name'
        ]);

        return array_map(static function (array $item): array {
            return [
                'name' => $item['name'],
                'key' => $item['key_'],
                'value' => $item['lastvalue'],
                'clock' => (int) $item['lastclock'],
                'units' => $item['units'],
                'state' => $item['state']
            ];
        }, $items);
    }

    private function getAvailability(array $interfaces): string
    {
        if (!$interfaces) {
            return 'unknown';
        }

        foreach ($interfaces as $interface) {
            if ((int) $interface['main'] === 1) {
                return $this->mapAvailability((int) $interface['available']);
            }
        }

        return $this->mapAvailability((int) $interfaces[0]['available']);
    }

    private function mapAvailability(int $available): string
    {
        return match ($available) {
            1 => 'available',
            2 => 'unavailable',
            default => 'unknown'
        };
    }

    private function getAvailabilityText(string $availability): string
    {
        return match ($availability) {
            'available' => _('Available'),
            'unavailable' => _('Unavailable'),
            default => _('Unknown')
        };
    }
}