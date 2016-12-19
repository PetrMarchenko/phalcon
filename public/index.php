<?php

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/functions.php';
set_time_limit(0);


error_reporting(-E_ALL);

try {
    $webLog = new \Phalcon\Logger\Adapter\File(LOGS_DIR . '/web.log');
    $webLog->setLogLevel(\Phalcon\Logger::NOTICE);
    $logger = new \Phalcon\Logger\Multiple();
    $logger->push($webLog);
    // Check if FirePHP is installed on client via User-Agent header
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP')
        || !empty($_SERVER['HTTP_X_FIREPHP']) || !empty($_SERVER['HTTP_X_FIREPHP_VERSION'])) {
        $logger->push(new \Phalcon\Logger\Adapter\Firephp(""));
    }
    $logger->debug('url: ' . $_SERVER['REQUEST_URI']);


    $di = new \Phalcon\DI\FactoryDefault();
    $di->setShared('config', function () {
        return require APP_DIR . '/config/config.php';
    });
    $di->setShared('logger', $logger);

    $application = new Shark\Application($di);
    $application->registerWebServices();

    echo $application->handle()->getContent();

} catch (Phalcon\Exception $e) {
    echo $e->getMessage();

    echo $e->getTraceAsString();
} catch (PDOException $e) {
    echo $e->getMessage();

    echo $e->getTraceAsString();
}
