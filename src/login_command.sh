name="${args[name]}"
password="${args[password]}"
output_format="${args[--output-format]}"
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

log_verbose "Kikoeru login response: ${response}"

http_code="${response##*$'\n'}"
body="${response%$'\n'*}"

# HTTP 200 Success
if [[ "$output_format" == "alfred" ]]; then
    if [[ "$http_code" == "200" ]]; then
        if token=$(jq -er '.token | strings | select(length > 0)' <<< "$body"); then
            jq -cn --arg token "$token" '{
              success: true,
              data: {token: $token},
              error: null
            }' >&1
        else
            echo '{
                  success: false,
                  data: null,
                  error: {
                    code: login_missing_token,
                    message: "Error: Missing token in response."
                  }
                }'
            exit 1
        fi
    else
        error=$(jq -r '.error // "Unknown error"' <<< "$body" 2>/dev/null || true)
        jq -cn \
            --arg message "$error" '{
              success: false,
              data: null,
              error: {
                code: null,
                message: $message
              }
            }' >&1
        exit 1
    fi
else
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
fi
