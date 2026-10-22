kikoeru_get_login() {
    local username="$KIKOERU_USERNAME"
    local password="$KIKOERU_PASSWORD"
    local cache_dir="$KIKOERU_CLI_CACHE_DIR"

    local kikoeru_api_url="$KIKOERU_API_ENDPOINT"

    local cache_file
    local expires_at=""
    local http_code
    local now
    local token=""
    local payload response body error

    now=$(date +%s) || return 1
    cache_file="${cache_dir}/auth.json"

    # Read cache
    if [[ -f "$cache_file" ]]; then
        verbose_log "Reading cache: $cache_file"
        if jq -e --arg username "$username" '
                .username == $username
                and (.token | type == "string" and length > 0)
                and (.expires_at | type == "number")
            ' "$cache_file" >/dev/null 2>&1; then

            token=$(jq -r '.token' "$cache_file") || return 1
            expires_at=$(jq -r '.expires_at' "$cache_file") || return 1

            # Use cache token
            if jq -en --argjson now "$now" --argjson expires_at "$expires_at" \
                '$now < $expires_at' >/dev/null; then
                verbose_log "Using cached token."
                printf '%s\n' "$token"
                return 0
            fi
        fi
    fi

    # Payload
    payload=$(jq -n \
        --arg name "$username" \
        --arg password "$password" \
        '{name: $name, password: $password}') || return 1

    verbose_log "POST $kikoeru_api_url/auth/me"
    # POST request
    if response=$(curl -sS \
        -A "fradeet/kikoeru-cli/$version" \
        --request POST \
        --header 'Content-Type: application/json' \
        --data "$payload" \
        --write-out $'\n%{http_code}' \
        "$kikoeru_api_url/auth/me"); then
        :
    else
        echo 'Error: Login request failed, unable to connect to API.' >&2
        return 1
    fi

    http_code="${response##*$'\n'}"
    body="${response%$'\n'*}"

    if [[ "$http_code" == "200" ]]; then
        verbose_log "Request succeeded, retrieving token"
        if token=$(jq -er '.token | strings | select(length > 0)' <<<"$body"); then
            verbose_log "Checking cache directory"
            if ! mkdir -p "$cache_dir"; then
                echo 'Error: failed to create cache directory.' >&2
                return 1
            fi

            verbose_log "Saving auth cache"
            if ! jq -n \
                --arg username "$username" \
                --arg token "$token" \
                --argjson expires_at "$((now + 86400))" \
                '{username: $username, token: $token, expires_at: $expires_at}' \
                >"$cache_file"; then
                echo 'Error: failed to save auth cache.' >&2
                return 1
            fi
            verbose_debug "Cache expires at $expires_at"
            verbose_log "Saved auth cache to $cache_file"

            # Output
            printf '%s\n' "$token"
        else
            echo "Error: Missing token in response." >&2
            return 1
        fi
    else
        error=$(jq -r '.error // "Unknown error"' <<<"$body" 2>/dev/null || true)
        printf 'Login failed（HTTP %s）：%s\n' "$http_code" "${error:-Unknown error}" >&2
        return 1
    fi
}
