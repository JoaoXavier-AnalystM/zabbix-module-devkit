#!/bin/sh
# Creates the PostgreSQL monitoring user used by the
# "PostgreSQL by Zabbix agent 2" template.
#
# Runs once, on first start of a fresh postgres_data volume.
# Environment variables are provided by docker compose.

set -eu

MONITOR_USER="${ZBX_MONITOR_USER:-zbx_monitor}"
MONITOR_PASSWORD="${ZBX_MONITOR_PASSWORD:-zbx_monitor_dev_password}"

psql -v ON_ERROR_STOP=1 \
    --username "$POSTGRES_USER" \
    --dbname "$POSTGRES_DB" <<-SQL
	DO \$\$
	BEGIN
		IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = '${MONITOR_USER}') THEN
			CREATE USER ${MONITOR_USER} WITH PASSWORD '${MONITOR_PASSWORD}';
		END IF;
	END
	\$\$;

	GRANT pg_monitor TO ${MONITOR_USER};
SQL
