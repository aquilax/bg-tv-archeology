<?php

$tv = [
	'parva-programa' => [
		'name' => 'Първа програма',
		'from' => '1959-12-26', 
		'to' => '1992-05-31',
		'lineage' => 'parva-programa',
	],
	'kanal-1' => [
		'name' => 'Канал 1',
		'from' => '1992-06-01',
		'to' => '2008-08-13',
		'lineage' => 'parva-programa',
	],
	'bnt-1' => [
	  'name' => 'БНТ 1',
		'from' => '2008-08-14',
		'to' => '',
		'lineage' => 'parva-programa',
	],
	'vtora-programa' => [
		'name' => 'Втора програма',
		'from' => '1975-09-09',
		'to' => '1992-05-31',
		'lineage' => 'vtora-programa',
	],
  'efir-2' => [
		'name' => 'Ефир 2',
		'from' => '1992-06-01',
		'to' => '2000-05-31',
		'lineage' => 'vtora-programa',
	],
	'bnt-2' => [
	  'name' => 'БНТ 2',
		'from' => '2011-10-16',
		'to' => '',
		'lineage' => 'vtora-programa',
	],
];

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

