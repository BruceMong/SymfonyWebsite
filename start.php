<?php
$old_path = getcwd();
chdir('/');
$output = shell_exec('./server.sh');
chdir($old_path);
var_dump($output);
