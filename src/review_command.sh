output_format="${args['--output-format']:-}"

request_args=()

if [[ -n "${args[--filter]:-}" ]]; then
    request_args+=(--data-urlencode "filter=${args[--filter]}")
fi

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
        -A "fradeet/kikoeru-cli/$version" \
        -H 'accept: application/json' \
        -H "authorization: Bearer $token" \
        --write-out $'\n%{http_code}' \
        "${request_args[@]}" \
        "$KIKOERU_API_ENDPOINT/review"
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

    log_verbose "Checking images cache..."

    # Cache Image
    image_dir="$KIKOERU_CLI_CACHE_DIR/work_cover_images/sam"
    mkdir -p "$image_dir" || exit 1

    images_arg=()
    for work in $(jq -r '.works[] | .id' <<<"$body"); do
        if [[ ! -f "$image_dir/$work.jpg" ]]; then
            images_arg+=(-o "$image_dir/$work.jpg" "${KIKOERU_API_ENDPOINT%/}/cover/$work?type=sam")
        fi
    done

    if ((${#images_arg[@]} > 0)); then
        log_verbose "Downloading new ${#images_arg[@]} cover images..."
        curl -sS "${images_arg[@]}" || exit 1
    fi

    alfred_script_filter_result=$(jq -c \
        --arg frontend_url "$KIKOERU_FRONTEND_URL" \
        --arg image_dir "$image_dir" \
        '{
            skipknowledge: true,
            cache: {
                seconds: 60
            },
            items: [
                .works.[] | {
                    uid: ("work-" + (.id | tostring)),
                    title: .title,
                    subtitle: [.tags.[].name] | join(", "),
                    arg: .id,
                    icon: {
                        path: ($image_dir + "/" + (.id | tostring) + ".jpg")
                    },
                    quicklookurl: ($frontend_url + "/work/" + (.id | tostring))
                }
            ]
    }' <<<"$body")

    echo "$alfred_script_filter_result"

else
    echo "$body"
fi
