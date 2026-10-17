output_format="${args['--output-format']:-}"

request_args=()

if [[ -n "${args[--order]:-}" ]]; then
    request_args+=(--data-urlencode "order=${args[--order]}")
fi

if [[ -n "${args[--sort]:-}" ]]; then
    request_args+=(--data-urlencode "sort=${args[--sort]}")
fi

if [[ -n "${args[--page]:-}" ]]; then
    request_args+=(--data-urlencode "page=${args[--page]}")
fi

token=$(kikoeru_get_login) || exit 1

response=$(
    curl -sS -G \
        -H 'accept: application/json' \
        -H "authorization: Bearer $token" \
        --write-out $'\n%{http_code}' \
        "${request_args[@]}" \
        "$KIKOERU_API_URL/review"
) || {
    echo 'cURL Error.' >&2
    exit 1
}

status_code="${response##*$'\n'}"
body="${response%$'\n'*}"

if [[ "$output_format" == "alfred" ]]; then
    if [[ "$status_code" != "200" ]]; then
        echo "Error: $status_code" >&2
        exit 1
    fi

    items=$(jq -c \
        --arg frontend_url "$KIKOERU_FRONTEND_URL" '{
            items: [
                .works.[] | {
                    uid: ("work-" + (.id | tostring)),
                    title: .title,
                    subtitle: [.tags.[].name] | join(", "),
                    arg: .id,
                    quicklookurl: ($frontend_url + "/work/" + (.id | tostring))
                }
            ]
    }' <<<"$body")

    echo "$items"

else
    echo "$body"
fi
