<?php

$defaultIncludes = [];
$bootstrapPath = __DIR__.'/lib/init_libs.php';
if (file_exists($bootstrapPath)) {
    $defaultIncludes[] = $bootstrapPath;
}

return [

    'defaultIncludes' => $defaultIncludes,

    'startupMessage' => '<info>Cmsium libraries are loaded. Have fun!</info>'

];