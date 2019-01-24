<?php
require __DIR__."/lib/converters/data_converters.php";
require __DIR__."/lib/headers.p/headers.php";
require __DIR__."/lib/errors.p/errors.php";
\define("TEST_ERR",["code"=>666,"message"=>"Test error"]);
use Lib\Errors as err;
err\renderError(TEST_ERR,false,["some content"]);