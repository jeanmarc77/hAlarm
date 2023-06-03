<?php
/**
 * /srv/http/halarm/scripts/alarm.php
 *
 * @package default
 */

include 'loadcfg.php';
while (true) { // To infinity ... and beyond!
	///// Memory
	if (file_exists($MEMORY)) {
		$data        = file_get_contents($MEMORY);
		$memarray1st = $data;
		$memarray    = json_decode($data, true);
	}
	$memarray['scenario'] = $scenario;
	if (!isset($memarray['status'])) {
		$memarray['status'] = 'startup';
		$memarray['msg'] = "$lgSTART";
	}
	if (!isset($memarray['5minflag'])) {
		$memarray['5minflag'] = false;
	}
	$now = date($DATEFORMAT . ' H:i:s');
	$datareturn = null;
	exec($REQCOMMAND, $datareturn);
	$json   = trim(implode($datareturn));
	$jarray = json_decode($json, true);

	if (json_last_error() == JSON_ERROR_NONE) {
		$giveup           = 0;
		$memarray['UTC']  = strtotime(date('Ymd H:i:s'));

		for ($i=0;$i<$cnti;$i++) {
			if (isset($jarray["I$i"]) && isset($I[$i])) {
				$memarray["I$i"]   = $jarray["I$i"];
			} else {
				$memarray["I$i"]   = null;
			}
		}
		for ($i=0;$i<$cnto;$i++) {
			if (isset($jarray["O$i"]) && isset($O[$i])) {
				$memarray["O$i"]   = $jarray["O$i"];
			} else {
				$memarray["O$i"]   = null;
			}
		}

		if (!$flagalarm && !$flagwarn && !$flagoff  && !$flagleav) {
			$memarray['status'] = 'Armed';
			$memarray['msg'] = "$lgUNDS";
			if(!$flagarm && $automate && $LCKARM) {
				$flagarm = true;
				$automatemsg['cmd'] = 'armed';
				pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
			}
		}
		if ($comlost) {
			$comlost    = false;
			$stringData = "$now\tConnection restored with centrale\n\n";
			logevents($stringData);
			if (!empty($POUKEY)) {
				$pushover = pushover($POAKEY, $POUKEY, "hAlarm", $stringData, 'gamelan');
			}
			if (!empty($TLGRTOK)) {
				$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
			}
		}
		// checking wrongs data messages
		for ($i=0;$i<$cnti;$i++) {
			if (isset($memarray["I$i"]) && $memarray["I$i"]!='off' && $memarray["I$i"]!='on' && !$ierr[$i]) {
				$ierr[$i]= true;
				$val = $memarray["I$i"];
				$stringData = "$now\tWrong value for $I[$i]: $val\n\n";
				logevents($stringData);
			} else {
				$ierr[$i]= false;
			}
		}
		for ($i=0;$i<$cnto;$i++) {
			if (isset($memarray["O$i"]) && $memarray["O$i"]!='off' && $memarray["O$i"]!='on' && !$oerr[$i]) {
				$oerr[$i] = true;
				$val = $memarray['O$i'];
				$stringData = "$now\tWrong value for $O[$i]: $val\n\n";
				logevents($stringData);
			} else {
				$oerr[$i] = false;
			}
		}
	} else {
		// reset values
		for ($i=0;$i<$cnti;$i++) {
			$memarray["I$i"]   = null;
		}
		for ($i=0;$i<$cnto;$i++) {
			$memarray["O$i"]   = null;
		}
		if ($giveup == 0) {
			logevents("$now\tCommunication error\n\n");
		}
		if (!$comlost && $giveup > 3) {
			$comlost    = true;
			$memarray['msg'] = "$lgCONL";
			$stringData = "$now\tConnection lost with centrale\n\n";
			logevents($stringData);
			if (!empty($POUKEY)) {
				$pushover = pushover($POAKEY, $POUKEY, "hAlarm Warning", $stringData, 'intermission');
			}
			if (!empty($TLGRTOK)) {
				$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
			}
		}
		if ($giveup > 3) {
		sleep(5);
		}
		$giveup++;
	}

	$nowutc = strtotime(date('Ymd H:i:s'));
	// Checking detectors and doing commands
	if ($giveup == 0) {
		$msg = '';
		// Entrances
		for ($i=0;$i<$cnte;$i++) {
			$PIDd = filemtime("$ADMDIR/scripts/alarm.pid");
			$entrance = $E[$i];
			if (($memarray["I$entrance"] == 'on' && !$INALARM)) {
				if ($nowutc - $PIDd > $TENTR) { // Entering home
					if (!$flagwarn) {
						//$flagleav = false;
						$flagwarn = true;
						$memarray['status'] = 'Warn';
						$memarray['msg'] = "$lgENTR";
						logevents("$now\t$I[$entrance] entering\n\n");
						$INWARN   = true;
						$warntime = strtotime(date('Ymd H:i:s'));
						if(isset($WARNCOMMAND)) {
						exec($WARNCOMMAND);
						}
						if($automate) {
						$automatemsg['cmd'] = 'warn';
						pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
						}
					}
				} else { // leaving
					if (!$flagleav) {
						logevents("$now\t$I[$entrance] leaving\n\n");
					}
				}
			}
		}
		// Leaving
		if (!$flagleav && $nowutc - $PIDd < $TENTR) {
			$flagleav = true;
			$memarray['status'] = 'Leaving';
			$memarray['msg'] = "$lgLEAV";
			if($automate) {
				$automatemsg['cmd'] = 'warn'; // wake all kekp
				pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
			}
		}
		if ($flagleav && $nowutc - $PIDd > $TENTR) {
			$flagleav = false;
		}
		if ($INWARN && $nowutc - $warntime > $TENTR) {
			$INWARN = false;
			if(isset($WARNOFF)) {
			exec($WARNOFF);
			}
			$INALARM = true;
		}
		for ($i=0;$i<$cnte;$i++) { // Entrances
			$entrance = $E[$i];
			if ($memarray["I$entrance"] == 'on' && $INALARM) {
				$msg .= "$I[$entrance] ";
			}
		}
		// All other det.
		for ($i=0;$i<$cnti;$i++) {
			if (!in_array($i, $E)) {
				if ($memarray["I$i"]=='on') {
				$INALARM = true;
				$msg .= "$I[$i] ";
				}
			}
		}

		if ($INALARM) {
			$memarray['status'] = 'Alarm';
			$memarray['msg'] = "$lgALRM";
			if (!empty($msg)) { // keep loggin dets
				if ($msg!=$prevmsg) {
					$stringData = "$now\tAlarm ! ($msg)\n\n";
					logevents($stringData);
				}
				$prevmsg = $msg;
			}
			if (!$flagalarm) {
				$flagalarm = true;
				$alarmtime = strtotime(date('Ymd H:i:s'));
				$cnt = count($ALARMCOMMAND);
				for ($i=0;$i<$cnt;$i++) {
					if(isset($ALARMCOMMAND[$i])) {
					exec($ALARMCOMMAND[$i]);
					}
				}
				
				$stringData = "$now\tAlarm ! ($msg)\n\n";
				if (!empty($POUKEY)) {
					$pushover = pushover($POAKEY, $POUKEY, "hAlarm Warning", $stringData, 'updown');
				}
				if (!empty($TLGRTOK)) {
					$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
				}
				if($automate) {
					$automatemsg['cmd'] = 'alarm';
					pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
				}
			}

			if (!$flagoff  && $nowutc - $alarmtime > $TAOFF) { // turn off sirens
				$flagoff  = true;
				$memarray['status'] = 'Auto off alarm';
				$memarray['msg'] = "$lgATOF";
				if(isset($ALARMOFF)) {
				exec($ALARMOFF);
				}
				$stringData = "$now\tAuto off alarm ! ($TAOFF sec)\n\n";
				logevents($stringData);
				if (!empty($POUKEY)) {
					$pushover = pushover($POAKEY, $POUKEY, "hAlarm Warning", $stringData, 'spacealarm');
				}
				if (!empty($TLGRTOK)) {
					$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
				}
			}
		} // in alarm
	} // Check

	$minute   = date('i');
	if (in_array($minute, $minlist) && !$memarray['5minflag']) { // 5 min jobs
		$memarray['5minflag'] = true;
		$memarray['msg'] .= " - $lg5T";
		// Keypads alive test
		$PIDd = filemtime("$ADMDIR/scripts/alarm.pid");
		if ($cntkyp > 0 && $nowutc - $PIDd > $TENTR) {
			$kpmsg = '';
			if	(file_exists($KYPMEM)) {
				$data        = file_get_contents($KYPMEM);
				$kbmemarray    = json_decode($data, true);
			}
			
			for($i=0; $i<$cntkyp; $i++) {
				$key = $KYP[$i];
				if (isset($kbmemarray[$key]['UTC'])) {
					if ($nowutc - $kbmemarray[$key]['UTC'] > 360) {
							$kbflag = true;
							$kpmsg .= $key .' ';
							if($automate) {
								$automatemsg['cmd'] = 'restart';
								$automatemsg['ident'] = $key;
								logevents("$now\tConnection lost with keypad $key, restarting\n\n");
								pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
							} else {
								logevents("$now\tConnection lost with keypad $key\n\n");
							}
					} 
				} else {
					if($automate) {
						$automatemsg['cmd'] = 'restart';
						$automatemsg['ident'] = $key;
						logevents("$now\tKeypad $key not active, restarting\n\n");
						pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
					} else {
						logevents("$now\tKeypad $key not active\n\n");
					}
				}
			}
						
			if ($kbflag && $kpmsg != $prevkpmsg && !empty($kpmsg)) {
				$stringData = "$now\tConnection lost with keypad(s) $kpmsg\n\n";
				if (!empty($POUKEY)) {
					$pushover = pushover($POAKEY, $POUKEY, "hAlarm Warning", $stringData, 'intermission');
				}
				if (!empty($TLGRTOK)) {
					$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
				}
				$prevkpmsg = $kpmsg;
			} elseif (empty($kpmsg) && $kbflag) {
				$kbflag = false;
				$stringData = "$now\tConnection restored with keypad(s)\n\n";
				logevents($stringData);
				if (!empty($POUKEY)) {
					$pushover = pushover($POAKEY, $POUKEY, "hAlarm", $stringData, 'gamelan');
				}
				if (!empty($TLGRTOK)) {
					$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
				}
				if($automate) { // keep awake
					$automatemsg['cmd'] = 'armed';
					pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
				}
			}
		} 

		// Network test
		$time = pingDomain($NETIP);
		if ($time == -1 && !$netflag) {
			$netflag     = true;
			$stringData = "$now\tConnection lost with internet\n\n";
			logevents($stringData);
		} else {
			if ($netflag) {
				$netflag     = false;
				$stringData = "$now\tConnection restored with internet\n\n";
				logevents($stringData);
				if (!empty($POUKEY)) {
					$pushover = pushover($POAKEY, $POUKEY, "hAlarm", $stringData, 'gamelan');
				}
				if (!empty($TLGRTOK)) {
					$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
				}
			}
		}
	} // 5min

	if (!in_array($minute, $minlist) && $memarray['5minflag']) { // Run once every 1,6,11,16,..
		$memarray['5minflag'] = false; // Reset 5minflag
	}

	$data = json_encode($memarray);
	if ($data != $memarray1st) { // Reduce write
		$data = json_encode($memarray);
		file_put_contents($MEMORY, $data);
	}

	usleep($DELAY);
} // infinity
?>
