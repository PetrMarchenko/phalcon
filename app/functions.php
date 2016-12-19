<?php
function shutdown()
{
    if ($error = error_get_last()) {
        switch ($error['type']) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_PARSE:
                $error = "[SHUTDOWN] lvl:" . $error['type'] . " | msg:" . $error['message']
                    . " | file:" . $error['file'] . " | ln:" . $error['line'];

                logError($error);
                break;
            default:
                logError($error);
        }
    }
}
function errorHandler($level, $message, $file, $line, $context)
{

    switch ($level) {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_PARSE:
            $type = "FATAL";
            break;
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
            $type = "ERROR";
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $type = "INFO";
            break;
        case E_STRICT:
            $type = "DEBUG";
            break;
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
        default:
            $type = "WARN";
    }
    $error = "[" . $type ."] lvl: " . $level . " | msg:" . $message . " | file:" . $file . " | ln:" . $line;

    logError($error);
}

function logError($error)
{
    $error =  "[" . date('H:i:s') . "]" . $error . \PHP_EOL;

    file_put_contents(\LOGS_DIR . '/error.log', $error, \FILE_APPEND);
}

register_shutdown_function('shutdown');
set_error_handler("errorHandler");