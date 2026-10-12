name="${args[name]}"
password="${args[password]}"
format="${args[--format]}"
kikoeru_api_url="$KIKOERU_API_URL"

# Payload
payload=$(jq -n \
    --arg name "$name" \
    --arg password "$password" \
    '{name: $name, password: $password}')

# POST request
if response=$(curl -sS \
    --request POST \
    --header 'Content-Type: application/json' \
    --data "$payload" \
    --write-out $'\n%{http_code}' \
    "$kikoeru_api_url/auth/me"); then
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
        if [[ "$format" == "alfred" ]]; then
            printf '%s\n' "$token"
        fi
    else
        echo "Error: Missing token in response." >&2
        exit 1
    fi
else
    error=$(jq -r '.error // "Unknown error"' <<< "$body" 2>/dev/null || true)
    printf 'Login failed（HTTP %s）：%s\n' "$http_code" "${error:-Unknown error}" >&2
    exit 1
fi
