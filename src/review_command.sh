output_format="${args['--output-format']:-}"
image_type="${args['--cache-image-type']}"

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

if [[ "$status_code" == "200" ]]; then
    log_verbose "Checking images cache..."

    image_dir="$KIKOERU_CLI_CACHE_DIR/work_cover_images/$image_type"
    mkdir -p "$image_dir" || exit 1

    images_arg=()
    for work in $(jq -br '.works[] | .id' <<<"$body"); do
        if [[ ! -f "$image_dir/$work.jpg" ]]; then
            verbose_debug "Caching image for work: $work (type: $image_type)"
            images_arg+=(-o "$image_dir/$work.jpg" "${KIKOERU_API_ENDPOINT%/}/cover/$work?type=$image_type")
        else
            verbose_debug "Image for work: $work (type: $image_type) is already cached"
        fi
    done

    if ((${#images_arg[@]} > 0)); then
        log_verbose "Downloading new ${#images_arg[@]} cover images..." # TODO Fix
        curl -sS "${images_arg[@]}" || exit 1
    fi
    if [[ "$output_format" == "alfred" ]]; then
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
    elif [[ "$output_format" == "quicker" ]]; then
        win_image_dir=$(cygpath -w "$image_dir")
        quicker_result=$(jq -c \
            --arg image_dir "$win_image_dir" \
            '{
            Items: [
                .works.[] | {
                    Title: .title,
                    Description: [.tags.[].name] | join(", "),
                    Icon: ($image_dir + "\\" + (.id | tostring) + ".jpg"),
                    Data: .id,
                    DataType: "custom",
                }
            ]
        }' <<<"$body")
        echo "$quicker_result"
    else
        echo "$body"
    fi
    echo "[Review] Request Error: HTTP $status_code" >&2
    exit 1
fi
