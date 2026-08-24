---
name: zabbix-frontend-modules
description: Use when creating, extending, reviewing, or debugging native Zabbix 7.x frontend modules written in PHP 8+, especially menus, actions, views, forms, tables, filters, modals, permissions, and module validation.
---

# Zabbix frontend modules

Use this skill together with the repository `AGENTS.md`. `AGENTS.md` has priority; this skill adds Zabbix-specific guidance.

## Before changing code

- Read `AGENTS.md`, inspect the working tree, and preserve unrelated user changes.
- Inspect the existing module and manifest before adding folders, dependencies, CSS, or JavaScript.
- For every UI feature, search the Zabbix frontend source or official module examples for an equivalent implementation first.
- When available, use local reference modules such as `Zabbix-Module-*-main` to compare structure and layout, but do not copy their insecure or compatibility-only patterns blindly.

## Preferred module shape

```text
Module.php                  menu registration and module initialization
manifest.json               metadata and action mapping
actions/                    controller validation, authorization, and orchestration
include/                    services, repositories, validators, helpers when justified
views/                      prepared data rendered with native Zabbix components
assets/                     only required JS/CSS, declared in the manifest
tests/                      focused regression and integration tests
```

Keep the flow `action -> service -> repository/API -> prepared view data`. Views must not perform API or database access.

## Native UI patterns

- Register menus through `APP::Component()->get('menu.main')` with `CMenu`, `CMenuItem`, and native submenu methods.
- Prefer native classes such as `CHtmlPage`, `CForm`, `CFormList`, `CTableInfo`, `CFilter`, `CButton`, `CUrl`, `CDiv`, and `CMessageHelper` after confirming the target Zabbix version supports them.
- Use translation functions for user-facing strings.
- Prefer server-side actions and native navigation over custom JavaScript.
- Add custom CSS or JavaScript only when no native pattern meets the requirement, and keep it isolated under `assets/`.

## Action and security checklist

- Map every action explicitly in `manifest.json`.
- Validate all request fields with controller validation and constrain IDs, enums, sort fields, pagination, and text lengths.
- Check the minimum required user role or permissions in `checkPermissions()` for every protected action.
- Keep CSRF enabled for state-changing actions. A read-only page should not disable CSRF without a documented reason.
- Use POST for mutations, native confirmation/modal patterns, and output escaping for every user-controlled value.
- Never expose passwords, tokens, macros, or secrets to views, logs, HTML, JavaScript, or repository files.
- Do not modify Zabbix core files or introduce Bootstrap, Tailwind, Material UI, or another frontend framework.

## Validation and review

Run the narrowest relevant checks first, then the repository gates:

- PHP syntax for every module PHP file;
- JSON parsing for every manifest;
- UTF-8 without BOM;
- forbidden-framework checks;
- tests and Docker/Zabbix validation when available.

Before completion, review architecture, native UI consistency, authorization, CSRF, XSS/output escaping, input validation, and deployment impact. Report anything that could only be verified on the live Zabbix frontend.

## Avoid copying blindly

Reference modules may contain useful layouts but also inline HTML/CSS, broad permissions, legacy-version fallbacks, disabled CSRF, or secrets passed into views. Treat them as visual and structural references, not authority over the project rules.
