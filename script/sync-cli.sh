#!/usr/bin/env bash

set -Eeuo pipefail

# Configuration
SOURCE="./kikoeru-cli"
WORKFLOW_DIR="./workflow"
TARGET="${WORKFLOW_DIR}/kikoeru-cli"

bashly generate

# Check source file
if [[ ! -f "$SOURCE" ]]; then
    printf 'Error: bashly not found: %s\n' "$SOURCE" >&2
    exit 1
fi

# Check Workflow directory
if [[ ! -d "$WORKFLOW_DIR" ]]; then
    printf 'Error: Workflow directory not found: %s\n' "$WORKFLOW_DIR" >&2
    exit 1
fi

# Move and ensure executable
mv -- "$SOURCE" "$TARGET"
chmod +x "$TARGET"

printf 'bashly installed to: %s\n' "$TARGET"
