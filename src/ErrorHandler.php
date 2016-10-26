<?php
namespace Garden;

class ErrorHandler {

    /**
     * init error handler
     */
    public static function register()
    {
        if (class_exists('\Kuria\Error\ErrorHandler')) {
            self::kuria();
        } else {
            // Make all errors into exceptions.
            set_error_handler('garden_error_handler', E_ALL);
        }
    }

    private static function kuria()
    {
        $handler = new \Kuria\Error\ErrorHandler( c('main.debug') );
        $handler->register();

        if ( c('main.logs') ) {
            $handler->on('fatal', function ($exception, $debug) {
                $logFilePath = PATH_LOGS.'/'.($debug ? 'debug' : 'prod').'/'.date('Y').'/'.date('m').'/';
                $old_umask = umask(0);
                @mkdir($logFilePath, 0775, true);
                umask($old_umask);

                $entry = sprintf(
                    "[%s] %s - %s in file %s on line %d\n",
                    date('Y-m-d H:i:s'),
                    \Kuria\Error\Util\Debug::getExceptionName($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                );

                file_put_contents($logFilePath.date('d').'.log', $entry, FILE_APPEND | LOCK_EX);
            });
        }
    }
}