# AGENTS.md

## Project purpose

This repository is a development kit for building native Zabbix frontend modules.

Primary targets:

- Zabbix 7.x
- PHP 8+
- Native Zabbix frontend architecture
- Reusable module development patterns
- Automated validation and testing
- AI-assisted development

---

## Core principles

1. Never modify Zabbix core files.
2. All functionality must be implemented as a frontend module.
3. Prefer native Zabbix UI components and patterns.
4. Do not introduce Bootstrap, Tailwind, Material UI or other CSS frameworks.
5. Avoid custom CSS when an equivalent Zabbix component exists.
6. Avoid custom JavaScript when native Zabbix behavior can be reused.
7. Keep business logic outside views.
8. Validate authorization and permissions on every protected action.
9. Validate and sanitize all user input.
10. Never commit secrets, API tokens or passwords.

---

## Architecture

Preferred dependency flow:

Controller / Action
        ↓
Service
        ↓
Repository / Zabbix API
        ↓
Zabbix

Views must never contain business logic.

Views must never perform database access.

Views should only receive prepared data from actions/controllers.

---

## Zabbix frontend development

Before implementing a frontend component:

1. Search the Zabbix frontend source for an equivalent implementation.
2. Reuse the same UI pattern whenever possible.
3. Follow native naming conventions.
4. Follow native form, table, modal and filtering patterns.
5. Prefer Zabbix API and frontend classes instead of direct database access.

When implementing:

- menus
- tables
- filters
- forms
- modals
- messages
- validation
- pagination

always inspect how Zabbix implements the equivalent feature.

---

## PHP

Target:

PHP >= 8.0

Guidelines:

- use strict and explicit code where appropriate
- avoid unnecessary abstractions
- prefer small classes
- avoid large controllers
- keep actions focused
- avoid duplicated logic
- use typed parameters and return values where compatible

---

## Security

Always review:

- authentication
- authorization
- CSRF
- XSS
- input validation
- output escaping
- filesystem access
- external HTTP requests
- secrets
- privilege escalation

Never trust request parameters.

---

## AI workflow

AI agents must follow this order for significant features:

1. Understand requirements
2. Inspect existing repository
3. Inspect equivalent Zabbix implementation
4. Produce implementation plan
5. Implement
6. Run validation
7. Perform code review
8. Perform Zabbix architecture review
9. Perform UI review
10. Perform security review
11. Update documentation

Do not implement large architectural changes without documenting the decision.

---

## Git workflow

Branches:

feat/<name>
fix/<name>
refactor/<name>
docs/<name>
test/<name>

Commit examples:

feat: add maintenance module
fix: validate module permissions
refactor: extract maintenance service
test: add module validation tests
docs: document native UI conventions

Keep commits small and focused.

---

## Definition of Done

A feature is complete only when:

- PHP syntax is valid
- module loads in Zabbix
- no Zabbix core files were modified
- native UI patterns are followed
- authorization was reviewed
- inputs are validated
- tests pass
- documentation is updated