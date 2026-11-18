<?php

// Each module keeps its routes in routes/modules/<name>.php so that
// parallel work never collides in one file.
foreach (glob(__DIR__.'/modules/*.php') as $file) {
    require $file;
}
