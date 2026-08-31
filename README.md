# Alfred Workflow PHP Template

A small PHP starter project for building Alfred Script Filter workflows. It keeps
the workflow's core logic separate from Alfred-specific JSON and output handling.

## Requirements

- macOS with [Alfred](https://www.alfredapp.com/)
- PHP 8.4.1 or later
- [Composer](https://getcomposer.org/)

## Installation

### Create a repository from the GitHub template

1. Open the
   [template repository](https://github.com/fradeet/alfred-workflow-php-template).
2. Select **Use this template**, then **Create a new repository**.
3. Clone your new repository and enter its directory:

   ```bash
   git clone https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git
   cd YOUR-REPOSITORY
   ```

4. Install the workflow dependencies:

   ```bash
   composer install --working-dir=workflow
   ```

### Create a project with Composer

Once the package is available on Packagist, create a project with:

```bash
composer create-project fradeet/alfred-workflow-php-template my-alfred-workflow
cd my-alfred-workflow
composer install --working-dir=workflow
```

## Run the example

Run the executable PHP adapter directly:

```bash
workflow/src/AlfredAdapters/Hello.php
```

The same adapter can also be invoked through PHP:

```bash
php workflow/src/AlfredAdapters/Hello.php
```

The command writes valid Alfred Script Filter JSON to standard output:

```json
{"items":[{"title":"Hello Alfred"}]}
```

PHP warnings and other displayed diagnostics are written to standard error so
they do not corrupt Alfred's JSON input.

CLI and business errors are also returned as Alfred Script Filter JSON, with a
non-zero exit status:

```json
{"items":[{"title":"Unable to Load Results","subtitle":"Open the debugger and try again","valid":false}]}
```

## Connect it to Alfred

Create a workflow in Alfred and add a **Script Filter** object. Configure it to
run the operation's executable adapter:

```bash
"$PWD/workflow/src/AlfredAdapters/Hello.php"
```

Connect the Script Filter to the actions needed by your workflow.

Each business operation has its own adapter. All positional arguments are
forwarded to that operation in order:

```bash
workflow/src/AlfredAdapters/Hello.php Ada Lovelace
php workflow/src/AlfredAdapters/Hello.php Ada Lovelace
```

Both commands output `{"items":[{"title":"Hello Ada Lovelace"}]}`.

## Package the workflow

Package the contents of `workflow/` as an Alfred workflow from the repository
root:

```bash
./workflow-packager
```

The command reads the workflow name from `workflow/info.plist` and creates
`<workflow-name>.alfredworkflow` in the repository root. Generated workflow
packages are ignored by Git. To choose another destination, pass the complete
output path:

```bash
./workflow-packager build/hello.alfredworkflow
```

The destination directory must already exist. If Composer development
dependencies are installed in `workflow/vendor`, the packager first runs
`composer install --no-dev` in `workflow/` so they are not included in the
package. The remaining packaging happens from a temporary copy, where
`.DS_Store` files are removed and variables listed in `variablesdontexport` are
cleared without modifying their source files.

## Customize the workflow

- Add one executable PHP file under `workflow/src/AlfredAdapters/` for each
  operation exposed to Alfred. Use a PascalCase business name and begin the file
  with `#!/usr/bin/env php`.
- Read Alfred inputs in the operation adapter, call the core class, and adapt its
  plain return value into the required Alfred response type.
- Put Alfred data classes and enums in `AlfredAdapters/Type`, one type per file.
  Put reusable input, output, environment, and error-handling functions in
  `AlfredAdapters/Support`.
- Put Alfred-independent business logic in `workflow/src/` under the
  `Workflow` namespace. Core classes should accept explicit inputs and
  return plain PHP values.
- After adding or renaming classes or Support files, refresh Composer's
  autoloader:

  ```bash
  composer dump-autoload --working-dir=workflow --optimize
  ```

## Development checks

Run the example and the project checks before committing changes:

```bash
workflow/src/AlfredAdapters/Hello.php
(cd workflow && vendor/bin/phpstan analyse src --debug --no-progress)
(cd workflow && vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no --sequential)
```

## License

This project is available under the MIT License.
