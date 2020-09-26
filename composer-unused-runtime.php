<?php

$installed = file_get_contents(__DIR__ . '/.installed.txt');
$installed = explode(PHP_EOL, $installed);
$installed = array_filter($installed);
sort($installed);

$usedRuntime = require __DIR__ . '/.composer-used-runtime.php';

$packagesUsedRuntime = [];
foreach ($usedRuntime as $file) {
    if (1 !== preg_match(sprintf('/^%s\/vendor\/(?<package>\w+\/\w+)\//', preg_quote(__DIR__, '/')), $file, $matches)) {
        continue;
    }

    $packagesUsedRuntime[$matches['package']] = true;
}
$packagesUsedRuntime = array_keys($packagesUsedRuntime);

echo 'The following packages will be installed in production, but are never used during the test suite:' . PHP_EOL . PHP_EOL;
print_r(array_diff($installed, $packagesUsedRuntime));
