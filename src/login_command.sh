# Payload
payload=$(jq -n \
    --arg name "${args[name]}" \
    --arg password "${args[password]}" \
    '{name: $name, password: $password}')

# POST request
if response=$(curl -sS \
    --request POST \
    --header 'Content-Type: application/json' \
    --data "$payload" \
    --write-out $'\n%{http_code}' \
    "$KIKOERU_API_URL/auth/me"); then
    :
else
    echo "Error: Login request failed, unable to connect to API." >&2
    exit 1
fi

http_code="${response##*$'\n'}"
body="${response%$'\n'*}"

# HTTP 200 Success
if [[ "$http_code" == "200" ]]; then
    if token=$(jq -er '.token | strings | select(length > 0)' <<< "$body"); then
        printf '%s\n' "$token"
    else
        echo "Error: Missing token in response." >&2
        exit 1
    fi
else
    error=$(jq -r '.error // "Unknown error"' <<< "$body" 2>/dev/null || true)
    printf 'Login failed（HTTP %s）：%s\n' "$http_code" "${error:-Unknown error}" >&2
    exit 1
fi
