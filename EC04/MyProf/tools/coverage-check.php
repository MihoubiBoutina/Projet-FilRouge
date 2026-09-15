<?php

$threshold = isset($argv[1]) ? (float) $argv[1] : 70.0;
$coverageFile = $argv[2] ?? __DIR__ . '/../coverage-xml/coverage.xml';

if (!is_file($coverageFile)) {
    fwrite(STDERR, "Coverage file not found: $coverageFile\n");
    exit(1);
}

$xml = simplexml_load_file($coverageFile);
if ($xml === false) {
    fwrite(STDERR, "Invalid coverage XML file: $coverageFile\n");
    exit(1);
}

$metrics = $xml->project->metrics;
if (!isset($metrics['line-rate'])) {
    fwrite(STDERR, "Coverage line-rate missing in $coverageFile\n");
    exit(1);
}

$rate = (float) $metrics['line-rate'];
$percentage = round($rate * 100, 2);

if ($rate * 100 < $threshold) {
    fwrite(STDERR, sprintf("Coverage %.2f%% is below the required %.2f%% threshold. CI blocked.\n", $percentage, $threshold));
    exit(1);
}

fwrite(STDOUT, sprintf("Coverage OK: %.2f%% (threshold %.2f%%)\n", $percentage, $threshold));
exit(0);
