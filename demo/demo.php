<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/image.php';

use Dolphin\Wang\Every\Image;

$image = new Image();

$image->save();
