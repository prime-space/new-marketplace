<?php namespace App\Guzzle;

use GuzzleHttp\Client;

class Guzzle extends Client
{
    private $env;

    public function __construct(string $env)
    {
        $this->env = $env;
        parent::__construct();
    }

    public function request($method, $uri = '', array $options = [])
    {
        if ('dev' === $this->env) {
            $domain = parse_url($uri, PHP_URL_HOST);
            if (1 === preg_match('/^.+\.wip$/i', $domain)) {
                if (!isset($options['curl'][CURLOPT_PROXY])) {
                    $options['curl'][CURLOPT_PROXY] =  '127.0.0.1';
                    $options['curl'][CURLOPT_PROXYPORT] =  '7080';
                }
            }
        }

        return parent::request($method, $uri, $options);
    }
}
