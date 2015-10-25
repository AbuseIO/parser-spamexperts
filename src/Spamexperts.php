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

                    $this->events[] = [
                        'source'        => config("{$this->configBase}.parser.name"),
                        'ip'            => $report['Source-IP'],
                        'domain'        => !empty($report['Source-Domain']) ? $report['Source-Domain'] : false,
                        'uri'           => false,
                        'class'         => config("{$this->configBase}.feeds.{$this->feedName}.class"),
                        'type'          => config("{$this->configBase}.feeds.{$this->feedName}.type"),
                        'timestamp'     => strtotime($report['Arrival-Date']),
                        'information'   => json_encode($report),
                    ];
                }
            }
        }

        return $this->success();
    }
}
