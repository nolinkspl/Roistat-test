<?php

namespace Service;

use Exception;

class UrlDataParser
{
    /** @var  string $url */
    private $url;

    /** @var  string $urlData */
    private $urlData = '';

    /** @var  array $juxtapositions */
    private $juxtapositions = [];

    /** @var array $replacementLog */
    private $replacementLog = [];

    public function __construct($url = '')
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrlData()
    {
        return $this->urlData;
    }

    /**
     * @param string $urlData
     */
    public function setUrlData($urlData)
    {
        $this->urlData = $urlData;
    }

    /**
     * @return array
     */
    public function getJuxtapositions()
    {
        return $this->juxtapositions;
    }

    /**
     * @param array $juxtapositions
     */
    public function setJuxtapositions(array $juxtapositions)
    {
        $this->juxtapositions = $juxtapositions;
    }

    /**
     * @return array
     */
    public function getReplacementLog()
    {
        return $this->replacementLog;
    }

    /**
     * @param array $replacementLog
     */
    public function setReplacementLog(array $replacementLog)
    {
        $this->replacementLog = $replacementLog;
    }

    /**
     * @return mixed|string
     */
    public function getDataByUrl()
    {
        $timeout = 10;
        $data = '';

        try {
            $data = $this->urlGetContentTimeout($this->getUrl(), $timeout);

            if (!$data) {
                throw new Exception('Url is unavailable');
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $this->setUrlData($data);

        return $data;
    }

    /**
     * @param $url
     * @param int $timeout
     *
     * @return mixed
     *
     * @throws Exception
     */
    private function urlGetContentTimeout($url, $timeout = 3)
    {
        if (strpos($url, "://") === false) {
            throw new Exception('That is not URL');
        }

        if (!function_exists("curl_init")) {
            throw new Exception('curl is not in dependencies');
        }

        $session = curl_init($url);

        curl_setopt($session, CURLOPT_MUTE, true);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($session, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($session, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible)");
        $result = curl_exec($session);

        curl_close($session);

        return $result;
    }

    /**
     * @param array $juxtapositions
     */
    public function addJuxtapositions(array $juxtapositions)
    {
        $juxtapositions = array_merge($this->getJuxtapositions(), $juxtapositions);
        $this->setJuxtapositions($juxtapositions);
    }

    /**
     * @param string $search
     * @param string $substitute
     */
    public function addJuxtaposition($search, $substitute)
    {
        $juxtapositions = array_merge($this->getJuxtapositions(), [$search => $substitute]);
        $this->setJuxtapositions($juxtapositions);
    }

    /**
     * @return bool
     */
    public function replace()
    {
        $count = 0;
        $data = $this->getUrlData();
        $replacements = [];

        foreach ($this->getJuxtapositions() as $search => $substitute) {
            preg_match($search, $data, $matches, PREG_OFFSET_CAPTURE);

            unset($matches[0]);

            $replacements[] = [
                'search' => $matches,
                'substitute' => $substitute,
            ];
            $data = preg_replace('/' . $search . '/m', $substitute, $data, -1, $count);
        }

        if (!$this->detectLooping($replacements)) {
            $this->setUrlData($data);
            $this->addReplacementsLog($replacements);
        }

        if ($count !== 0) {
            $this->replace();
        }

        return true;
    }

    /**
     * @param $replacements
     * @return bool
     */
    private function detectLooping($replacements)
    {
        foreach ($this->getReplacementLog() as $log) {
            if ($replacements === $log) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $log
     */
    private function addReplacementsLog(array $log)
    {
        $log = array_merge($this->getReplacementLog(), $log);
        $this->setReplacementLog($log);
    }

    /**
     * @return bool
     */
    public function recover()
    {
        $dataBackUpper = new UrlDataBackUpper();
        $dataBackUpper->setUrlData($this->getUrlData());
        $dataBackUpper->setReplacementLog($this->getReplacementLog());
        $this->setUrlData($dataBackUpper->replaceBack());

        return true;
    }
}