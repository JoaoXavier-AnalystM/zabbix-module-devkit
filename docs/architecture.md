# Architecture

## Goal

Provide a reusable development environment for native Zabbix frontend modules.

## Supported stack

- Zabbix 7.x
- PHP 8+
- PostgreSQL
- Docker
- JavaScript
- PHPUnit
- Playwright

## Module architecture

Each module should follow:

modules/
└── ModuleName/
    ├── manifest.json
    ├── Module.php
    ├── actions/
    ├── views/
    ├── include/
    │   ├── Services/
    │   ├── Repositories/
    │   ├── Validators/
    │   └── Helpers/
    ├── assets/
    │   ├── js/
    │   └── css/
    └── tests/

## Request flow

User
 ↓
Zabbix frontend
 ↓
Module action
 ↓
Validation / Authorization
 ↓
Service
 ↓
Zabbix API
 ↓
View
 ↓
Native Zabbix UI

## Monitoring

The development stack monitors itself:

- `zabbix-agent2` container with read-only Docker socket access reports
  container, host and PostgreSQL metrics to the local Zabbix server.
- The agent listens on port `10150` by default. `ZBX_AGENT_PORT` controls both
  the agent listen port and the host interface port registered by the bootstrap.
- Passive checks are enabled for the Compose service `zabbix-server`, supplied
  by `ZBX_SERVER_HOST` without duplicating it in `ZBX_PASSIVESERVERS`.
- Deploys recreate only `zabbix-server`, `zabbix-agent2` and `zabbix-web`;
  PostgreSQL and its volume are preserved and verified by container ID.
- `scripts/bootstrap-monitoring.sh` registers the `DevKit-Stack` host via
  the Zabbix API with the Linux, Docker and PostgreSQL agent 2 templates.
- The PostgreSQL monitoring user (`zbx_monitor`, role `pg_monitor`) is
  created by `docker/postgres/initdb/01-zbx-monitor.sh` on first start.

## Main rule

The module must behave as if it were part of the native Zabbix frontend.

External UI frameworks are not allowed.

## AI-assisted workflow

Architect
 ↓
Developer
 ↓
Code Reviewer
 ↓
Zabbix Reviewer
 ↓
UI Reviewer
 ↓
Security Reviewer
 ↓
Tests
 ↓
Human Review