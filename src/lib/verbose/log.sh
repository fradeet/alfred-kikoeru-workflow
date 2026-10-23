verbose_log() {
  (( ${args[--verbose]:-0} >= 1 )) || return 0
  printf '[INFO] %s\n' "$*" >&2
}
