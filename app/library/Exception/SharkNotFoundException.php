<?php

namespace Shark\Library\Exception;

use Exception;

class SharkNotFoundException extends Exception
{
    protected $message = 'Page not found';
    protected $code = 404;

}