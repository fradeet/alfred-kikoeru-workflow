output_format="${args['--output-format']:-}"
image_type="${args['--cache-image-type']}"

request_args=()

if [[ -n "${args[--filter]:-}" ]]; then
    verbose_debug "Filter: ${args[--filter]}"
    request_args+=(--data-urlencode "filter=${args[--filter]}")
fi

if [[ -n "${args[--order]:-}" ]]; then
    verbose_debug "Order: ${args[--order]}"
    request_args+=(--data-urlencode "order=${args[--order]}")
fi

if [[ -n "${args[--sort]:-}" ]]; then
    verbose_debug "Sort: ${args[--sort]}"
    request_args+=(--data-urlencode "sort=${args[--sort]}")
fi

if [[ -n "${args[--page]:-}" ]]; then
    verbose_debug "Page: ${args[--page]}"
    request_args+=(--data-urlencode "page=${args[--page]}")
fi

token=$(kikoeru_get_login) || exit 1

verbose_log "GET $KIKOERU_API_ENDPOINT/review"
response=$(
    curl -sS -G \
        -A "fradeet/kikoeru-cli/$version" \
        -H 'accept: application/json' \
        -H "authorization: Bearer $token" \
        --write-out $'\n%{http_code}' \
        "${request_args[@]}" \
        "$KIKOERU_API_ENDPOINT/review"
) || exit 1

verbose_debug "Response: $response"

status_code="${response##*$'\n'}"
body="${response%$'\n'*}"

if [[ "$status_code" == "200" ]]; then
    verbose_log "Checking images cache..."

    image_dir="$KIKOERU_CLI_CACHE_DIR/work_cover_images/$image_type"
    verbose_log "Image dir: $image_dir"
    mkdir -p "$image_dir" || exit 1

    images_arg=()
    for work in $(jq -br '.works[] | .id' <<<"$body"); do
        if [[ ! -f "$image_dir/$work.jpg" ]]; then
            verbose_debug "Image need cache: work id $work."
            images_arg+=(-o "$image_dir/$work.jpg" "${KIKOERU_API_ENDPOINT%/}/cover/$work?type=$image_type")
        fi
    done

    if ((${#images_arg[@]} > 0)); then
        curl -sS --remove-on-error --parallel-max 3 \
            -A "fradeet/kikoeru-cli/$version" \
            -H "authorization: Bearer $token" \
            "${images_arg[@]}" || exit 1
        verbose_log "Downloaded new ${#images_arg[@]}/3 cover images..." # TODO Fix
    fi
    if [[ "$output_format" == "alfred" ]]; then
        verbose_log "Generating Alfred Script Filter result..."
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
        verbose_log "Generating Quicker result..."
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
else
    verbose_debug "Response: $body"
    echo "[Review] Request Error: HTTP $status_code" >&2
    exit 1
fi
