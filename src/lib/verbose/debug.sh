verbose_debug() {
  (( ${args[--verbose]:-0} >= 2 )) || return 0
  printf '[DEBUG] %s\n' "$*" >&2
}
