<?php

require('common.php');

$dataDir = $argv[1]; // data dir
$dataDir = realpath($dataDir);
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

function getTimeSort($time) {
  list($h, $m) = explode(':', $time);
  // TV Schedule starts at 5 every day
  return ((($h - 5 + 24) % 24 ) * 60) + $m;
}

function parseFile($fileName) {
  $result = [];
  $handle = fopen($fileName, "r");
  $date = getDateFromFileName($fileName);
  $channel = getChannel($fileName);
  while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if ($line[0] == '#') {
      continue;
    }
    $row = str_getcsv($line, "\t");
    if ($row) {
      if ($row[0] === "time") {
        continue;
      }
      $time = trim(array_shift($row));
      if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
        fwrite(STDERR, sprintf("Invalid time in (%s): `%s`\n", $fileName, $time));
      }
      $result[] = [
        $channel,
        $date,
        $time,
        implode(' ', $row),
      ];
    }
  }
  fclose($handle);
  return $result;
}

$allRows = [];

$rdi = new \RecursiveDirectoryIterator($dataDir);
$rii = new \RecursiveIteratorIterator($rdi);
foreach ($rii as $fileInfo) {
  if ($fileInfo->isFile()) {
    $result = parseFile($fileInfo->getPathname());
    if ($result) {
      $allRows = array_merge($allRows, $result);
    }
  }
}


usort($allRows, function($a, $b) {
  // date; channel; time
  return [$a[1], $a[0], getTimeSort($a[2])] <=> [$b[1], $b[0], getTimeSort($b[2])];
});

$header = ['channel', 'date', 'time', 'name'];

$f = fopen("php://output", "w");
fputcsv($f, $header);
foreach($allRows as $row) {
  fputcsv($f, $row);
}
fclose($f);
