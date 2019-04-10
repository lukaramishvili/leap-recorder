<?php

$frames = $_POST['frames'];
$name = $_POST['name'];

file_put_contents("data/$name-".time().".ldat", $frames);

?>