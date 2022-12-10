<?php

require('common.php');

$dataDir = $argv[1]; // data dir
$dataDir = realpath($dataDir);
if (!$dataDir) {
  die("Data dir `{$dataDir}` not found" . PHP_EOL);
}

$errors = [];

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


$rdi = new \RecursiveDirectoryIterator($dataDir);
$rii = new \RecursiveIteratorIterator($rdi);
foreach ($rii as $fileInfo) {
  if ($fileInfo->isFile()) {
    $fileName = $fileInfo->getPathname();
    getDateFromFileName($fileName);
    getChannel($fileName);

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
      if ($row) {
        if (count($row) != 2) {
          $errors[] = sprintf("Invalid number of columns in (%s): `%s`\n", $fileName, $line);
        }
        if ($row[0] === "time") {
          continue;
        }
        $time = trim($row[0]);
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
          $errors[] = sprintf("Invalid time in (%s): `%s`\n", $fileName, $time);
        }
        if (!trim($row[1])) {
          $errors[] = sprintf("Empty name in (%s): `%s`\n", $fileName, $line);
        }
      }
    }
    fclose($handle);
  }
}

if (count($errors) > 0) {
  fwrite(STDERR, implode("\n", $errors));
  exit(1);
}
