<?php

namespace AbuseIO\Parsers;

class Spamexperts extends Parser
{
    /**
     * Create a new Spamexperts instance
     */
    public function __construct($parsedMail, $arfMail)
    {
        parent::__construct($parsedMail, $arfMail, $this);
    }

    /**
     * Parse attachments
     * @return Array    Returns array with failed or success data
     *                  (See parser-common/src/Parser.php) for more info.
     */
    public function parse()
    {

    }
}
