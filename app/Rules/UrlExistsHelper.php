<?php

namespace App\Rules;


class UrlExistsHelper
{
    private $ch;

    public function __construct()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER , 1);
    }

    public function isExists(string $url): bool
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $response = curl_exec($this->ch);
        $status_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        curl_close($this->ch);

        return $status_code === 200;
    }
}
