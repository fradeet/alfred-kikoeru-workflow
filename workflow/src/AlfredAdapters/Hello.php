#!/usr/bin/env php
<?php

declare(strict_types=1);

use Workflow\AlfredAdapters\Type\AlfredSF;
use Workflow\AlfredAdapters\Type\AlfredSFItem;
use Workflow\Hello;

use function Workflow\AlfredAdapters\Support\alfredEnvironmentVariable;
use function Workflow\AlfredAdapters\Support\run;

require dirname(__DIR__, 2).'/vendor/autoload.php';

run(
    fn (array $arguments): AlfredSF => new AlfredSF(
        items: [
            new AlfredSFItem(
                title: (new Hello())(...$arguments),
                subtitle: alfredEnvironmentVariable('alfred_workflow_name'),
            ),
        ],
    ),
);
