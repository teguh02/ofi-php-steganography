<?php
require_once __DIR__ . "/vendor/autoload.php";

use Ofi\steganography\steganography;

// Encrypt message to image and download image
//steganography::encrypt("I'm trying to study php steganography");
// or
//steganography::encrypt("I'm trying to study php steganography", "Text to image Here");

// After file are downloaded, please move the file 
// to one folder from this index.php file

//Decrypt message from image
$result = steganography::decrypt('1623984118.png');
var_dump($result);