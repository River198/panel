<?php

namespace river\Exceptions\Service\Database;

use river\Exceptions\riverException;

class DatabaseClientFeatureNotEnabledException extends riverException
{
    public function __construct()
    {
        parent::__construct('Client database creation is not enabled in this Panel.');
    }
}
