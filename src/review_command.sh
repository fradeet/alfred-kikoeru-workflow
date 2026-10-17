output_format="${args['--output-format']}"

args=()

if [[ -n "${args[--order]}" ]]; then
    args+=(--data-urlencode "order=${args[--order]}")
fi

if [[ -n "${args[--sort]}" ]]; then
    args+=(--data-urlencode "sort=${args[--sort]}")
fi

if [[ -n "${args[--page]}" ]]; then
    args+=(--data-urlencode "page=${args[--page]}")
fi

response=$(
    curl -sS -G \
        -H 'accept: application/json' \
        -H "authorization: Bearer $(kikoeru_get_login)" \
        --write-out $'\n%{http_code}' \
        "${args[@]}" \
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
