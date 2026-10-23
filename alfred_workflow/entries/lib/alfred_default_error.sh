alfred_default_error() {
    error=$(jq -cn '{items:
        [{"title":"Error: Unable to Load Results",
        "subtitle":"Open the debugger and try again",
        "valid":false}]}')
    echo "$error"
    return 1
}
