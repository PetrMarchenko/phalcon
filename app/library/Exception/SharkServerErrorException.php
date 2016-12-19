<?php

namespace Shark\Library\Exception;

use Exception;

class SharkServerErrorException extends Exception
{
    protected $message = 'Server error';
    protected $code = 500;

}