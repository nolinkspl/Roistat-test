<?php

namespace Service;

class UrlDataBackUpper extends UrlDataParser
{
    /**
     * @return string
     */
    public function replaceBack()
    {
        $reversedLog = $this->reverseReplacementLog();
        $reversedUrlData = strrev($this->getUrlData());

        foreach ($reversedLog as $log) {
            foreach ($log['search'] as $search) {
                $substitutionLength = strlen($log['substitution']);
                $offset = strlen($reversedUrlData) - $search[1] - $substitutionLength + 1;
                $reversedUrlData = substr_replace(
                    $reversedUrlData,
                    $search,
                    $offset,
                    $substitutionLength
                );
            }
        }

        $this->setUrlData(strrev($reversedUrlData));

        return $this->getUrlData();
    }

    /**
     * @return array
     */
    private function reverseReplacementLog()
    {
        $reversedLog = [];

        foreach ($this->getReplacementLog() as $log) {
            $reversedLog[] = [
                'search' => array_reverse($log['search']),
                'substitute' => $log['substitute'],
            ];
        };

        return array_reverse($reversedLog);
    }
}
