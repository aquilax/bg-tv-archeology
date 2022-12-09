<?php

require('common.php');

$startDate = '1959-12-26';
$endDate = '2001-01-01';

$period = new DatePeriod(
  new DateTime($startDate),
  new DateInterval('P1D'),
  new DateTime($endDate)
);

$dataDir = $argv[1]; // data dir
$dataDir = realpath($dataDir);
if (!$dataDir) {
  die("Data dir `{$dataDir}` not found" . PHP_EOL);
}

foreach ($period as $value) {
  $path = implode('/', [
    $dataDir,
    $value->format('Y'),
    $value->format('m'),
    $value->format('d'),
  ]);
  if (!file_exists($path)) {
    mkdir($path, 0777, true);
  }
  $sDate = $value->format('Y-m-d');
  foreach($tv as $tvId => $tvDetails) {
    if ($tvDetails['from'] <= $sDate && ($tvDetails['to'] == '' || $tvDetails['to'] >= $sDate)) {
      $header = implode("\n", [
        "# $sDate TV Schedule for {$tvDetails['name']}",
        "time\ttitle",
      ])	. "\n";
      $fileName = $tvId . '.tsv';
      $fullFileName = implode('/', [$path, $fileName]);
      if (!file_exists($fullFileName)) {
        file_put_contents($fullFileName, $header);
        echo $sDate . "\t" . $fullFileName . PHP_EOL;
      }
    }
  }
}
