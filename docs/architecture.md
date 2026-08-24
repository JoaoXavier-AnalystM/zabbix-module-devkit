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