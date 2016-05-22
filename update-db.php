#!/usr/bin/php
<?php

$solar = json_decode(file_get_contents("/dev/shm/solar.txt"),TRUE);

$currentTime = time();
$sampleTime=strtotime($solar['sampleTime']);

$timeDiff=abs($currentTime-$sampleTime);

if ($timeDiff > 5*60) {
   echo "Last sample time more than 5 minutes ago - restarting vbus\n";
   mail ("hburke@gmail.com","VBUS daemon dead","The VBUS daemon has not updated in $timeDiff seconds\n","From: Home Automation <ha@burketech.com>");
   exec("/usr/sbin/service vbus stop");
   exec("/usr/sbin/service vbus start");
}

if ($solar['tempCollector'] == '999.9') {
   $solar['tempCollector'] = '32';
}
if ($solar['tempPool'] == '999.9') {
   $solar['tempPool'] = '32';
}

$dbh = new PDO('mysql:host=localhost;dbname=homeautomation', 'root', 'PASSWORD GOES HERE');
$sth = $dbh->prepare('INSERT INTO solarThermal (sampleTime,tempCollector,tempTank1,tempTank2,tempPool,speedPump,valveTank1,valveTank2,valvePool) VALUES (:sampleTime,:tempCollector,:tempTank1,:tempTank2,:tempPool,:speedPump,:valveTank1,:valveTank2,:valvePool)');
$sth->execute($solar);

?>
