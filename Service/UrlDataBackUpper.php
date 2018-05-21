<?php

namespace Service;

class UrlDataBackUpper extends UrlDataParser
{
    /** @var  string $modifiedData */
    private $modifiedData;

    public function __construct($url = '', $modifiedData)
    {
        parent::__construct($url);
        $this->modifiedData = $modifiedData;
    }
}