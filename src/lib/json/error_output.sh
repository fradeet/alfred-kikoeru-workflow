json_error_output() {
    jq -cn --arg code "$1" --arg message "$2" '{
      success: false,
      data: null,
      error: {
        code: $code,
        message: $message
      }
    }' >&1
}
