<?php

class ParabdException extends RuntimeException
{
    public $errorCode;
    public $fields;

    public function __construct($errorCode, $message, $fields = array())
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->fields = $fields;
    }
}

