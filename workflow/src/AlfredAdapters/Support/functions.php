<?php

declare(strict_types=1);

namespace Workflow\AlfredAdapters\Support;

use Workflow\AlfredAdapters\Type\AlfredSF;
use Workflow\AlfredAdapters\Type\AlfredSFItem;

/** Read a value from Alfred's script environment. */
function alfredEnvironmentVariable(string $name): ?string
{
    $value = getenv($name);

    return is_string($value) ? $value : null;
}

/**
 * Read the environment variables supplied by Alfred.
 *
 * @see https://www.alfredapp.com/help/workflows/script-environment-variables/
 *
 * @return array<string, null|string>
 */
function alfredEnvironmentVariables(): array
{
    $names = [
        'alfred_preferences',
        'alfred_preferences_localhash',
        'alfred_theme',
        'alfred_theme_background',
        'alfred_theme_selection_background',
        'alfred_theme_subtext',
        'alfred_version',
        'alfred_version_build',
        'alfred_workflow_bundleid',
        'alfred_workflow_cache',
        'alfred_workflow_data',
        'alfred_workflow_name',
        'alfred_workflow_description',
        'alfred_workflow_uid',
        'alfred_workflow_version',
        'alfred_debug',
        'alfred_workflow_keyword',
    ];

    $variables = [];

    foreach ($names as $name) {
        $variables[$name] = alfredEnvironmentVariable($name);
    }

    return $variables;
}

/** @return list<string> */
function cliArguments(): array
{
    $arguments = $_SERVER['argv'] ?? null;

    if (!is_array($arguments)) {
        throw new \RuntimeException('The Alfred adapter must be run from the command line.');
    }

    $cliArguments = [];

    foreach (array_slice($arguments, 1) as $argument) {
        if (!is_string($argument)) {
            throw new \RuntimeException('CLI arguments must be strings.');
        }

        $cliArguments[] = $argument;
    }

    return $cliArguments;
}

/** Encode an Alfred response as JSON. */
function toAlfredJson(\JsonSerializable $response): string
{
    return json_encode(
        $response,
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    );
}

/** Build the standard result shown when an adapter fails. */
function errorResponse(): AlfredSF
{
    return new AlfredSF(
        items: [
            new AlfredSFItem(
                title: 'Unable to Load Results',
                subtitle: 'Open the debugger and try again',
                valid: false,
            ),
        ],
    );
}

/**
 * Run an Alfred adapter with validated positional arguments.
 *
 * @param callable(list<string>): \JsonSerializable $adapter
 */
function run(callable $adapter): void
{
    error_reporting(E_ALL);
    ini_set('display_errors', 'stderr');

    try {
        echo toAlfredJson($adapter(cliArguments()));
    } catch (\Throwable) {
        echo toAlfredJson(errorResponse());

        exit(1);
    }
}
