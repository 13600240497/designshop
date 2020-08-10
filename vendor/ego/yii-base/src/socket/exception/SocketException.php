<?php
namespace ego\socket\exception;

use Exception;

class SocketException extends Exception
{

    function __construct($message)
    {
        parent::__construct($message);

    }
}
