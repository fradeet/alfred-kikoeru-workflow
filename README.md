# Kikoeru Workflow

## Setup

You need `curl`, `jq` to run the CLI, and Alfred Powerpack to run the workflow.

## Usage

For Alfred workflow usage instructions, see [alfred_workflow/README.md][] .

## Development

Before developing, install `bashly`.

### Workflow Readme

Follow the [Alfred Gallery Workflow Style Guide][].


Edit [alfred_workflow/README.md][] and run `./script/sync_readme.sh` to update the workflow readme.

### CLI

Before editing the CLI, run `./script/sync_cli.sh` to update the CLI to workflow.

[alfred_workflow/README.md]: /alfred_workflow/README.md
[Alfred Gallery Workflow Style Guide]: https://alfred.app/submit/styleguide/
