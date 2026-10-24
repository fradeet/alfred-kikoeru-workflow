# Kikoeru Workflow

## Setup

You need `curl` and `jq` to use the CLI, and Alfred Powerpack to run the workflow.

## Usage

For Alfred workflow usage instructions, see [alfred_workflow/README.md][].

## Development

Before developing, install [bashly][] to develop the CLI.

### Workflow README

Follow the [Alfred Gallery Workflow Style Guide][].

Edit [alfred_workflow/README.md][] and run `./script/sync_readme.sh` to update the workflow README.

### CLI

Before editing the CLI, run `./script/sync-cli.sh` to build the CLI and copy it into the workflow.

[alfred_workflow/README.md]: alfred_workflow/README.md
[bashly]: https://bashly.dev/
[Alfred Gallery Workflow Style Guide]: https://alfred.app/submit/styleguide/
