<?php

use App\DataTable;

require __DIR__  . '/../vendor/autoload.php';

$value = (new DataTable)->prompt();

var_dump($value);
