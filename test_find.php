<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$org = App\Models\Organization::first();
try {
    $res = App\Models\Organization::findOrFail($org);
    echo 'OK';
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage();
} catch (\Error $e) {
    echo "Error: " . $e->getMessage();
}
