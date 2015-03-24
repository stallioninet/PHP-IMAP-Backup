<?php

if (!file_exists('config.php')) {
	die('Please add a config.php - perhaps rename the sample-config.php');
}

require_once('config.php');


$imap = imap_open($connectionString, $username, $password, OP_READONLY, 10)
or die("can't connect: " . imap_last_error());



$folders = imap_list($imap, $connectionString, "*");

imap_close($imap);


$backupFile = fopen('./backups/'. date('Y-m-d_H-i-s'). '.xml', 'w+');

fwrite($backupFile, '<backup>');


fwrite($backupFile, '<folders>');

echo "Backing up:\n";

foreach ($folders as $folder) {


	$folderPath = str_replace($connectionString, "", $folder);

	echo $folderPath. ": ";

	fwrite($backupFile, '<folder path="'.$folder.'">');

	$mailbox = imap_open($folder, $username, $password, OP_READONLY, 10);

	$MC = imap_check($mailbox);

	echo "$MC->Nmsgs mails to backup: ";

	$result = imap_fetch_overview($mailbox,"1:{$MC->Nmsgs}",0);

	foreach ($result as $overview) {

		fwrite($backupFile, '<message number="'.$overview->msgno.'">');

		echo '.';

		$message = imap_fetchheader($mailbox, $overview->msgno) . imap_body($mailbox, $overview->msgno);

		fwrite($backupFile, '<![CDATA['.$message.']]>');

		fwrite($backupFile, '</message>');

	}

	echo "done\n";

	fwrite($backupFile, '</folder>');

	imap_close($mailbox);

}

fwrite($backupFile, '</folders>');
fwrite($backupFile, '</backup>');
fclose($backupFile);