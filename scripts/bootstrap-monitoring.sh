#!/usr/bin/env bash
#
# Registers the "DevKit-Stack" host in the local Zabbix frontend so the
# stack monitors itself. Idempotent: safe to run more than once.
#
# Requirements: curl (available on Linux and Git Bash for Windows).
#
# Environment variables:
#   ZABBIX_API_URL         default http://localhost:8080/api_jsonrpc.php
#   ZABBIX_API_USER        default Admin
#   ZABBIX_API_PASSWORD    default zabbix
#   ZBX_HOSTNAME           default DevKit-Stack
#   ZBX_AGENT_DNS          default zabbix-agent2
#   ZBX_AGENT_PORT         default 10150
#   ZBX_MONITOR_USER       default zbx_monitor
#   ZBX_MONITOR_PASSWORD   default zbx_monitor_dev_password
#   ZBX_MONITOR_DATABASE   default zabbix

set -euo pipefail

API_URL="${ZABBIX_API_URL:-http://localhost:8080/api_jsonrpc.php}"
API_USER="${ZABBIX_API_USER:-Admin}"
API_PASSWORD="${ZABBIX_API_PASSWORD:-zabbix}"
HOST="${ZBX_HOSTNAME:-DevKit-Stack}"
AGENT_DNS="${ZBX_AGENT_DNS:-zabbix-agent2}"
AGENT_PORT="${ZBX_AGENT_PORT:-10150}"
MONITOR_USER="${ZBX_MONITOR_USER:-zbx_monitor}"
MONITOR_PASSWORD="${ZBX_MONITOR_PASSWORD:-zbx_monitor_dev_password}"
MONITOR_DATABASE="${ZBX_MONITOR_DATABASE:-zabbix}"

api_call() {
    curl -sS -H 'Content-Type: application/json-rpc' -d "$1" "$API_URL"
}

echo "Waiting for Zabbix API at $API_URL ..."
AUTH=""
RESPONSE=""
for _ in $(seq 1 60); do
    RESPONSE=$(api_call "{\"jsonrpc\":\"2.0\",\"method\":\"user.login\",\"params\":{\"username\":\"${API_USER}\",\"password\":\"${API_PASSWORD}\"},\"id\":1}")
    AUTH=$(printf '%s' "$RESPONSE" | sed -nE 's/.*"result":"([^"]+)".*/\1/p')
    if [ -n "$AUTH" ]; then
        break
    fi
    sleep 5
done

if [ -z "$AUTH" ]; then
    echo "ERROR: could not authenticate against Zabbix API." >&2
    echo "Last response: $RESPONSE" >&2
    exit 1
fi
echo "Authenticated."

# Host group -------------------------------------------------------------

GROUP=$(api_call "{\"jsonrpc\":\"2.0\",\"method\":\"hostgroup.get\",\"params\":{\"output\":[\"groupid\"],\"filter\":{\"name\":[\"DevKit\"]}},\"auth\":\"${AUTH}\",\"id\":2}")
GROUPID=$(printf '%s' "$GROUP" | sed -nE 's/.*"groupid":"([0-9]+)".*/\1/p')

if [ -z "$GROUPID" ]; then
    CREATED=$(api_call "{\"jsonrpc\":\"2.0\",\"method\":\"hostgroup.create\",\"params\":{\"name\":\"DevKit\"},\"auth\":\"${AUTH}\",\"id\":3}")
    GROUPID=$(printf '%s' "$CREATED" | sed -nE 's/.*"groupids":\["([0-9]+)"\].*/\1/p')
    if [ -z "$GROUPID" ]; then
        echo "ERROR: could not create host group DevKit." >&2
        echo "Response: $CREATED" >&2
        exit 1
    fi
    echo "Host group DevKit created (id $GROUPID)."
fi

# Templates ----------------------------------------------------------------

template_id() {
    local name=$1
    api_call "{\"jsonrpc\":\"2.0\",\"method\":\"template.get\",\"params\":{\"output\":[\"templateid\"],\"filter\":{\"name\":[\"${name}\"]}},\"auth\":\"${AUTH}\",\"id\":4}" \
        | sed -nE 's/.*"templateid":"([0-9]+)".*/\1/p'
}

LINUX_TID=$(template_id "Linux by Zabbix agent active")
DOCKER_TID=$(template_id "Docker by Zabbix agent 2")
PG_TID=$(template_id "PostgreSQL by Zabbix agent 2")

if [ -z "$LINUX_TID" ] || [ -z "$DOCKER_TID" ] || [ -z "$PG_TID" ]; then
    echo "ERROR: one or more templates not found:" >&2
    echo "  Linux by Zabbix agent active: ${LINUX_TID:-MISSING}" >&2
    echo "  Docker by Zabbix agent 2:     ${DOCKER_TID:-MISSING}" >&2
    echo "  PostgreSQL by Zabbix agent 2: ${PG_TID:-MISSING}" >&2
    exit 1
fi

# Host ---------------------------------------------------------------------

EXISTS=$(api_call "{\"jsonrpc\":\"2.0\",\"method\":\"host.get\",\"params\":{\"output\":[\"hostid\"],\"selectInterfaces\":[\"interfaceid\"],\"filter\":{\"host\":[\"${HOST}\"]}},\"auth\":\"${AUTH}\",\"id\":5}")
if printf '%s' "$EXISTS" | grep -q '"hostid"'; then
    HOSTID=$(printf '%s' "$EXISTS" | sed -nE 's/.*"hostid":"([0-9]+)".*/\1/p')
    INTERFACEID=$(printf '%s' "$EXISTS" | sed -nE 's/.*"interfaceid":"([0-9]+)".*/\1/p')
    UPDATE=$(api_call "{\"jsonrpc\":\"2.0\",\"method\":\"hostinterface.update\",\"params\":{\"interfaceid\":\"${INTERFACEID}\",\"port\":\"${AGENT_PORT}\"},\"auth\":\"${AUTH}\",\"id\":7}")
    if ! printf '%s' "$UPDATE" | grep -q '"interfaceid"'; then
        echo "ERROR: could not update agent port for host $HOST (hostid $HOSTID)." >&2
        echo "Response: $UPDATE" >&2
        exit 1
    fi
    echo "Host $HOST already registered; agent interface updated to port $AGENT_PORT."
    exit 0
fi

BODY="{\"jsonrpc\":\"2.0\",\"method\":\"host.create\",\"params\":{"
BODY+="\"host\":\"${HOST}\",\"name\":\"${HOST}\","
BODY+="\"groups\":[{\"groupid\":\"${GROUPID}\"}],"
BODY+="\"interfaces\":[{\"type\":1,\"main\":1,\"useip\":0,\"dns\":\"${AGENT_DNS}\",\"port\":\"${AGENT_PORT}\"}],"
BODY+="\"templates\":[{\"templateid\":\"${LINUX_TID}\"},{\"templateid\":\"${DOCKER_TID}\"},{\"templateid\":\"${PG_TID}\"}],"
BODY+="\"macros\":["
BODY+="{\"macro\":\"{\$PG.CONNSTRING}\",\"value\":\"tcp://postgres:5432\"},"
BODY+="{\"macro\":\"{\$PG.USER}\",\"value\":\"${MONITOR_USER}\"},"
BODY+="{\"macro\":\"{\$PG.PASSWORD}\",\"value\":\"${MONITOR_PASSWORD}\"},"
BODY+="{\"macro\":\"{\$PG.DATABASE}\",\"value\":\"${MONITOR_DATABASE}\"}"
BODY+="]},"
BODY+="\"auth\":\"${AUTH}\",\"id\":6}"

RESULT=$(api_call "$BODY")

if printf '%s' "$RESULT" | grep -q '"hostids"'; then
    echo "Host $HOST registered with group DevKit and monitoring templates."
else
    echo "ERROR: host.create failed." >&2
    echo "Response: $RESULT" >&2
    exit 1
fi
