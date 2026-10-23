#!/usr/bin/env bash
. ./entries/lib/alfred_default_error.sh
export KIKOERU_CLI_CACHE_DIR="$alfred_workflow_cache"

result=$(./kikoeru-cli favourite \
    --output-format alfred \
    --filter listening \
    --order updated_at \
    --sort desc) || {
    alfred_default_error
    exit 1
}

echo "$result"
