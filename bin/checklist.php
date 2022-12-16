<?php

require('common.php');

$dataDir = $argv[1]; // data dir
if (!$dataDir) {
  die("Data dir `{$dataDir}` not found" . PHP_EOL);
}

function getDateFromFileName($fileName) {
  $segments = explode('/', $fileName);
  array_pop($segments); // file_name
  $day = array_pop($segments);
  $month = array_pop($segments);
  $year = array_pop($segments);
  $time = strtotime("{$year}-{$month}-{$day}");
  return date('Y-m-d', $time);
}

function getChannel($fileName) {
  global $tv;

  $channelId = basename($fileName, '.tsv');
  return $tv[$channelId]['name'];
}


$report = [];

$rdi = new \RecursiveDirectoryIterator($dataDir);
$rii = new \RecursiveIteratorIterator($rdi);
foreach ($rii as $fileInfo) {
  if ($fileInfo->isFile()) {
    $fileName = $fileInfo->getPathname();
    $date = getDateFromFileName($fileName);
    $channel = getChannel($fileName);
    $hasContent = false;
    $handle = fopen($fileName, "r");
    while (($line = fgets($handle)) !== false) {
      $line = trim($line);
      if (!$line) {
        continue;
      }
      if ($line[0] == '#') {
        continue;
      }
      $row = str_getcsv($line, "\t");
      if ($row[0] == 'time') {
        continue;
      }
      if ($row) {
        $hasContent = true;
        break;
      }
    }
    $report[] = [
      'date' => $date,
      'channel' => $channel,
      'hasContent' => $hasContent,
      'file' => $fileName,
    ];
    fclose($handle);
  }
}

$grouped = [];
foreach ($report as $row) {
  $date = $row['date'];
  if (!isset($grouped[$date])) $grouped[$date] = [];
  $grouped[$date][] = $row;
}
ksort($grouped);

// usort($report, function($a, $b) { return [$a['date'], $a['channel']] <=> [$b['date'], $b['channel']]; });

echo "# Checklist\n";
$year = '';
foreach($grouped as $date => $rows) {
  $newYear = substr($date, 0, 4);
  if ($newYear != $year) {
    echo "\n## {$newYear}\n\n";
    $year = $newYear;
  }
  $tvs = array_reduce($rows, function($carry, $item) {
    $carry[] = sprintf("[%s] [%s](%s)", $item['hasContent'] ? 'x' : ' ', $item['channel'], $item['file']);
    return $carry;
  }, []);
  sort($tvs);
  printf("* %s %s\n", $date, implode(' ', $tvs));
}
