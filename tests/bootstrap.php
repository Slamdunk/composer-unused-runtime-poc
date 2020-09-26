<?php

register_shutdown_function(static function () {
    $files = get_included_files();
    sort($files);

    file_put_contents(
        dirname(__DIR__) . '/.composer-unused-runtime.php',
        '<?php return ' . var_export($files, true)
    );
});
