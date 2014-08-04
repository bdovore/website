<?php
include_once('inc/util.inc.php');
echo '<pre>';
$date= $_GET['date'];


function completeDate ($date)
{
	if (preg_match('#^[0-9]{4}\-[0-9]{2}$#', $date))
	{
		$date .= '-01';
	}
	if (preg_match('#^[0-9]{2}\/[0-9]{4}$#', $date))
	{
		$date = '01/' . $date;
	}
	if (preg_match('#^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$#', $date))
	{
		$date = date("Y-m-d",TimestampDate($date));
	}
	
	return $date;
}

echo completeDate ($date);



echo '</pre>';
