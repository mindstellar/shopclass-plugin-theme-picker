#!/usr/bin/env bash
# Unit tests: no database, no network. Core's Demo class is read from
# SHOPCLASS_CORE, or ../osclass.
set -u
cd "$(dirname "$0")/.."
fail=0
for t in tests/*.php; do
    echo "── $t"
    php "$t" | tail -4
    [ "${PIPESTATUS[0]}" -eq 0 ] || fail=1
done
exit "$fail"
