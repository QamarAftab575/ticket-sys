<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

exit($kernel->handle(
    $input = new \Symfony\Component\Console\Input\ArrayInput([
        'command' => 'migrate',
    ]),
    new \Symfony\Component\Console\Output\ConsoleOutput()
));
