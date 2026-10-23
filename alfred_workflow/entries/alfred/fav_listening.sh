#!/usr/bin/env bash
export KIKOERU_CLI_CACHE_DIR="$alfred_workflow_cache"

./kikoeru-cli favourite --output-format alfred --filter listening --order updated_at --sort desc
