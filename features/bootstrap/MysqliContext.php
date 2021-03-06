<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;

class MysqliContext implements Context, SnippetAcceptingContext
{
    use FeatureContext;
    use MySQLContext;

    private function params(Array $params)
    {
        $params['driver'] = 'mysqli';
        return $params;
    }
}
