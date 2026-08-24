# ADR-001: Use native Zabbix UI components

## Status

Accepted

## Context

Modules created by this project must provide a consistent user experience with the Zabbix frontend.

Third-party UI frameworks can introduce visual inconsistencies and maintenance problems.

## Decision

Frontend modules must reuse native Zabbix UI patterns whenever possible.

The following frameworks must not be introduced:

- Bootstrap
- Tailwind CSS
- Material UI

Custom CSS and JavaScript should only be introduced when native Zabbix functionality cannot satisfy the requirement.

## Consequences

Advantages:

- native user experience
- fewer dependencies
- better compatibility with Zabbix
- easier maintenance

Trade-offs:

- developers must study existing Zabbix frontend implementations
- some components may require deeper understanding of Zabbix internals