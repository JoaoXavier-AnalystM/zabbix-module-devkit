# Zabbix Status

The screen displays the operational status of the built-in `Zabbix server` host.

It is a read-only native Zabbix frontend module. The action requires an
authenticated Zabbix user with the native `ui.monitoring.hosts` permission; the service reads the host interface status and
items whose keys start with `zabbix[`, and up to 50 active problems.

When the host or process items are unavailable, the screen renders an explicit
empty or unknown state instead of exposing API errors or sensitive data.

## Local validation

From the repository root:

```text
php -l modules/DevKitDemo/actions/DevKitDemo.php
php -l modules/DevKitDemo/include/Services/ZabbixServerStatusService.php
php -l modules/DevKitDemo/views/devkitdemo.view.php
```

The runtime smoke test requires the Docker Compose stack described in
`docker/compose.yml`. The target frontend is `http://localhost:8080`.