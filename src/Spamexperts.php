<?php

namespace AbuseIO\Parsers;
use AbuseIO\Models\Incident;

/**
 * Class Spamexperts
 * @package AbuseIO\Parsers
 */
class Spamexperts extends Parser
{
    /**
     * Create a new Spamexperts instance
     *
     * @param \PhpMimeMailParser\Parser $parsedMail phpMimeParser object
     * @param array $arfMail array with ARF detected results
     */
    public function __construct($parsedMail, $arfMail)
    {
        parent::__construct($parsedMail, $arfMail, $this);
    }

    /**
     * Parse attachments
     * @return array    Returns array with failed or success data
     *                  (See parser-common/src/Parser.php) for more info.
     */
    public function parse()
    {
        if ($this->arfMail !== true) {
            $this->feedName = 'default';

            // If feed is known and enabled, validate data and save report
            if ($this->isKnownFeed() && $this->isEnabledFeed()) {

                // Build up the report
                preg_match_all(
                    "/([\w\-]+): (.*)[ ]*\r?\n?\r\n/m",
                    $this->arfMail['report'],
                    $matches
                );

                $report = array_combine($matches[1], $matches[2]);

                // Sanity check
                if ($this->hasRequiredFields($report) === true) {

                    // Grap the domain and user from the authentication results for contact lookup (byDomain)
                    preg_match(
                        "/smtp.auth=(?<user>.*)@(?<domain>.*)/m",
                        $report['Authentication-Results'],
                        $matches
                    );

                    if (!empty($matches) && is_array($matches) && !empty($matches[0])) {
                        $report['Source-User'] = $matches['user'];
                        $report['Source-Domain'] = $matches['domain'];
                    }

                    ksort($report);

                    // Event has all requirements met, filter and add!
                    $report = $this->applyFilters($report);

                    $incident = new Incident();
                    $incident->source      = config("{$this->configBase}.parser.name");
                    $incident->source_id   = false;
                    $incident->ip          = $report['Source-IP'];
                    $incident->domain      = !empty($report['Source-Domain']) ? $report['Source-Domain'] : false;
                    $incident->uri         = false;
                    $incident->class       = config("{$this->configBase}.feeds.{$this->feedName}.class");
                    $incident->type        = config("{$this->configBase}.feeds.{$this->feedName}.type");
                    $incident->timestamp   = strtotime($report['Arrival-Date']);
                    $incident->information = json_encode($report);

                    $this->events[] = $incident;

                }
            }
        }

        return $this->success();
    }
}
