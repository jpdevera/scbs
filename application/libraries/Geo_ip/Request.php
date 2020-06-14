<?php

namespace Geo_ip;

use Exception;
use Geo_ip\Exception\Forbidden;

/**
 * @author Sujip Thapa <support@sujipthapa.co>
 */
class Request
{
    const ACCESS_KEY = '2e7c28464d844f1f07afa37e0986d8e2';
    /**
     * @var string
     */
    protected $ip;

    /**
     * @param $ip
     */
    public function __construct($ip = null)
    {
        $this->ip = $ip;
    }

    /**
     * @return null
     */
    public function make()
    {
        if (empty($this->ip)) {
            $this->throwException('No IP or hostname is provided', 403);
        }

        try {
            $response = file_get_contents(
                sprintf('http://api.ipstack.com/%s?access_key=%s', $this->ip, self::ACCESS_KEY)
            );
        } catch (Exception $e) {
            $this->throwException('Forbidden', 403);
        }

        return new Response($response);
    }

    /**
     * @param $message
     * @param $code
     */
    public function throwException($message, $code = 400)
    {
        throw new Forbidden($message, $code);
    }
}
