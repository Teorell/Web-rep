#!/usr/bin/php
<?PHP
$fd = fopen("/var/run/utmpx", "r");
date_default_timezone_set('Europe/Moscow');
while ($str = fread($fd, 628))
{
	$src = unpack("a256user/a4id/a32line/ipid/itype/Itime", $str);
	if($src[type] == 7)
	{
		echo substr($src[user], 0, strpos($src[user], 0))." ";
		echo substr($src[line], 0, strpos($src[line], 0))." ";
		echo date(" M  j H:i", $src[time])."\n";
	}
}