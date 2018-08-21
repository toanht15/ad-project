<?php

namespace App\Exceptions;


class APIRequestException extends \Exception
{

    protected $apiErrors = [];

    public function __construct($errors = [])
    {
        parent::__construct();
        $this->setApiErrors($errors);
    }

    public function setApiErrors($errors)
    {
        $this->apiErrors = $errors;
    }

    public function getApiErrors()
    {
        return $this->apiErrors;
    }
}