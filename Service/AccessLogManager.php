<?php

namespace Service;

class AccessLogManager
{
    const PATH = '../logs/';
    const LOG_NAME = 'access.log';
    const RESULT_TEMPLATE = [
        'views' => 0,
        'urls' => 0,
        'traffic' => 0,
        'crawlers' => [
            'Google' => 0,
            'Bing' => 0,
            'Baidu' => 0,
            'Yandex' => 0,
        ],
        'statusCodes' => [],
    ];
    const IP_PATTERN = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})';
    const URL_PATTERN = '(((POST)|(GET)) [\/][a-zA-Z0-9\/.\?\=]*)';
    const TRAFFIC_PATTERN = '([0-9]{3}[\ ](([0-9]{1,20})|([\-][\ ][0-9]{1,20})))';
    const CRAWLERS_NAMES = [
        'Google' => 'Googlebot',
        'Bing' => 'Bingbot',
        'Baidu' => 'Baidubot',
        'Yandex' => 'Yandexbot',
    ];

    /**
     * @return string
     */
    public function parseLog()
    {
        $stream = fopen(self::PATH . self::LOG_NAME, "r");
        $result = self::RESULT_TEMPLATE;

        if ($stream) {
            $data = [
                'ips' => [],
                'urls' => [],
            ];

            while (($line = fgets($stream)) !== false) {
                $ip = $this->getIPFromLine($line);

                if (!empty($line)) {
                    $data['ips'][] = $ip;
                    $result['views']++;
                }

                $url = $this->getUrlFromLine($line);

                if (!in_array($url, $data['urls'])) {
                    $data['urls'][] = $url;
                    $result['urls']++;
                }

                $traffic = $this->getTrafficVolumeFromLine($line);

                $result['traffic'] += $traffic['volume'];

                if(!array_key_exists($traffic['status'], $result['statusCodes'])) {
                    $result['statusCodes'][$traffic['status']] = 1;
                } else {
                    $result['statusCodes'][$traffic['status']] += 1;
                }

                $crawler = $this->getCrawlerNameFromLine($line);

                if (!empty($crawler)) {
                    $result['crawlers'][$crawler] += 1;
                }
            }

            fclose($stream);
        } else {
            echo "failed to open stream";
        }

        return json_encode($result);
    }

    /**
     * @param string $line
     * @return string
     */
    private function getIPFromLine($line)
    {
        preg_match(self::IP_PATTERN, $line, $matches);

        return $matches[0];
    }

    /**
     * @param string $line
     * @return string
     */
    private function getUrlFromLine($line)
    {
        preg_match(self::URL_PATTERN, $line, $matches);
        $url = explode(' ', $matches[0], $limit = 2);

        return $url[1];
    }

    /**
     * @param string $line
     * @return array
     */
    private function getTrafficVolumeFromLine($line)
    {
        preg_match(self::TRAFFIC_PATTERN, $line, $matches);
        $traffic = explode(' ', str_replace('- ', '', $matches[0]));
        $result = [
            'status' => $traffic[0],
            'volume' => $traffic[1],
        ];

        return $result;
    }

    /**
     * @param string $line
     * @return string|null
     */
    private function getCrawlerNameFromLine($line)
    {
        foreach (self::CRAWLERS_NAMES as $crawlerSystem => $name) {

            if (strpos($line, $name)) {
                return $crawlerSystem;
            }
        }

        return null;
    }
}