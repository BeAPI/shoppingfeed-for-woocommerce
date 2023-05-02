<?php
namespace ShoppingFeed\Sdk\Resource;

class Json
{
    /**
     * Encode in JSON with native function and throw exception on fail
     *
     * @param $content
     *
     * @return string
     */
    public static function encode($content)
    {
        $json = json_encode($content);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_encode error: ' . json_last_error_msg()
            );
        }

        return $json;
    }

    /**
     * Decode JSON string with native function and throw exception on fail
     *
     * @param string $json
     *
     * @return mixed
     */
    public static function decode($json, $assoc = false)
    {
        $content = json_decode($json, $assoc);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_decode error: ' . json_last_error_msg()
            );
        }

        return $content;
    }
}
