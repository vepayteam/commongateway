<?php

$base_key = '1234567890';
$shmKey = ftok(__FILE__, 't');

$mp = shmop_open($shmKey, 'c', 0600, 255);
shmop_write($mp, $base_key, 0);
shmop_close($mp);

$mp = shmop_open($shmKey, 'a', 0600, 255);
$key = trim(shmop_read($mp, 0, 255));
assert($key == $base_key, 'Keys are not equal');
shmop_close($mp);

$mp = shmop_open($shmKey, 'a', 0600, 255);
shmop_delete($mp);
shmop_close($mp);
