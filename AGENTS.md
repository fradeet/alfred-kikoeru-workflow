# Project Organization Conventions

## Two-Layer Architecture

Keep the call direction strictly one-way:

```text
Alfred -> executable PHP adapter -> core class
```

### 1. Alfred Adapter Layer

- Put Alfred-facing PHP adapters directly in `workflow/src/AlfredAdapters/`.
- Provide one executable adapter for each business operation that Alfred can invoke. Use a PascalCase business name, for example `workflow/src/AlfredAdapters/Hello.php`.
- Start each adapter with `#!/usr/bin/env php`, make it executable, and resolve files relative to its own directory so execution does not depend on the current working directory.
- Read CLI input and Alfred environment variables in this layer, then pass required values explicitly to the core class.
- Load core classes and Alfred types through `workflow/vendor/autoload.php`;
- Convert plain core return values into Alfred response types and write the encoded response to standard output.
- Keep adapters business-specific and thin. Do not put reusable business rules in them.

Reusable Alfred-specific code lives below the adapter directory:

- Put each Alfred response data class or enum in its own file under `workflow/src/AlfredAdapters/Type/` using the `Workflow\AlfredAdapters\Type` namespace.
- Put shared adapter functions under `workflow/src/AlfredAdapters/Support/` using the `Workflow\AlfredAdapters\Support` namespace.
- Register shared function files through Composer's `autoload.files`; do not require them from individual adapters.
- Keep CLI validation, Alfred environment access, JSON encoding, common error responses, and output handling in Support.

### 2. Core Layer

- Keep all logic that has no direct dependency on Alfred in `workflow/src/`, outside `AlfredAdapters/`.
- Place each core class in the `Workflow` namespace and follow PSR-4: the class name must match the filename.
- Core classes must not read Alfred environment variables or depend on Alfred user data implicitly. Pass required values to them explicitly.
- Core classes must return plain PHP values. They must not contain Alfred response structures, JSON fields, or console output logic.

## Loading and Running

- Composer maps `Workflow\` to `workflow/src/` and loads Support functions as configured in `workflow/composer.json`.
- Invoke an adapter directly as `workflow/src/AlfredAdapters/<Operation>.php [argument ...]`. Every positional argument belongs to that business operation and its boundary must be preserved.
- Calling the same entry through PHP, for example `php workflow/src/AlfredAdapters/Hello.php`, must also work.
- After adding or renaming classes or Support files, run `composer dump-autoload --working-dir=workflow --optimize`.
- Run the Hello example with `workflow/src/AlfredAdapters/Hello.php`.

## Alfred Output

- On successful execution, standard output must contain valid Alfred JSON without logs or debugging text.
- CLI and business errors must be converted to valid Alfred error JSON on standard output and return a non-zero exit status.
- Encode JSON with `JSON_THROW_ON_ERROR` so encoding failures are not silently ignored.
- Script Filter results belong in the top-level `items` array and use `title` for their title.
- The current Hello example outputs `{"items":[{"title":"Hello Alfred"}]}`.

## Verification

Run at least the following commands after making changes:

```bash
find workflow/src -name '*.php' -print0 | xargs -0 -n1 php -l
workflow/src/AlfredAdapters/Hello.php
workflow/src/AlfredAdapters/Hello.php "Ada Lovelace" "and team"
php workflow/src/AlfredAdapters/Hello.php "Ada Lovelace" "and team"
composer validate --strict --no-check-publish workflow/composer.json
(cd workflow && vendor/bin/phpstan analyse src --debug --no-progress)
(cd workflow && vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no --sequential)
```
