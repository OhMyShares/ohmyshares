<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Report import job timeout
    |--------------------------------------------------------------------------
    |
    | Importing a broker report runs in the background queue (see
    | App\Jobs\ImportReport) and can take a while for large reports. This is
    | the number of seconds a single import is allowed to run before the
    | worker kills it and the user is notified. Keep QUEUE_RETRY_AFTER
    | greater than this value.
    |
    */
    'timeout' => (int)env('REPORT_IMPORT_TIMEOUT', 900),

];
