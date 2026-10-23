api_endpoint="${KIKOERU_API_ENDPOINT%/}"
frontend_url="${KIKOERU_FRONTEND_URL%/}"
image_type="${args['--cache-image-type']}"
output_format="${args['--output-format']:-}"

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

verbose_log "GET $api_endpoint/review"
response=$(
    curl -sS -G \
        -A "fradeet/kikoeru-cli/$version" \
        -H 'accept: application/json' \
        -H "authorization: Bearer $token" \
        --write-out $'\n%{http_code}' \
        "${request_args[@]}" \
        "$api_endpoint/review"
) || {
    echo '[Favourite] Review request failed.' >&2
    exit 1
}

verbose_debug "Response: $response"

status_code="${response##*$'\n'}"
body="${response%$'\n'*}"

if [[ "$status_code" == "200" ]]; then
    verbose_log "Checking images cache..."

    image_dir="$KIKOERU_CLI_CACHE_DIR/work_cover_images/$image_type"
    verbose_log "Image dir: $image_dir"
    mkdir -p "$image_dir" || {
        echo '[Favourite] Failed to create cover cache directory.' >&2
        exit 1
    }

    images_arg=()
    work_ids=$(jq -br '.works[] | .id' <<<"$body") || {
        echo '[Favourite] Failed to read work IDs from review response.' >&2
        exit 1
    }
    for work in $work_ids; do
        if [[ ! -f "$image_dir/$work.jpg" ]]; then
            verbose_debug "Image need cache: work id $work."
            images_arg+=(-o "$image_dir/$work.jpg" "$api_endpoint/cover/$work?type=$image_type")
        fi
    done

    if ((${#images_arg[@]} > 0)); then
        curl -sS --fail --remove-on-error --parallel \
            -A "fradeet/kikoeru-cli/$version" \
            -H "authorization: Bearer $token" \
            "${images_arg[@]}" || {
            echo '[Favourite] Cover download failed.' >&2
            exit 1
        }
        image_count=$((${#images_arg[@]} / 3))
        verbose_log "Downloaded new $image_count cover images..."
    fi
    if [[ "$output_format" == "alfred" ]]; then
        verbose_log "Generating Alfred Script Filter result..."
        alfred_script_filter_result=$(jq -c \
            --arg frontend_url "$frontend_url" \
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
        }' <<<"$body") || {
            echo '[Favourite] Failed to generate Alfred result.' >&2
            exit 1
        }

        echo "$alfred_script_filter_result"
    elif [[ "$output_format" == "quicker" ]]; then
        verbose_log "Generating Quicker result..."
        win_image_dir=$(cygpath -w "$image_dir") || {
            echo '[Favourite] Failed to convert cover cache path for Quicker.' >&2
            exit 1
        }
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
        }' <<<"$body") || {
            echo '[Favourite] Failed to generate Quicker result.' >&2
            exit 1
        }
        echo "$quicker_result"
    else
        echo "$body"
    fi
else
    printf '[Favourite] Review request failed (HTTP %s): %s\n' "$status_code" "$body" >&2
    exit 1
fi
