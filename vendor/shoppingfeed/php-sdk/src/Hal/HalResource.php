<?php
namespace ShoppingFeed\Sdk\Hal;

class HalResource
{
    /**
     * @var array
     */
    private $properties = [];

    /**
     * @var array
     */
    private $links = [];

    /**
     * @var array
     */
    private $embedded = [];

    /**
     * @param HalClient $client
     * @param array     $data
     *
     * @return static
     */
    public static function fromArray(HalClient $client, array $data)
    {
        $links    = [];
        $embedded = [];

        if (isset($data['_links'])) {
            $links = $data['_links'];
            unset($data['_links']);
        }

        if (isset($data['_embedded'])) {
            $embedded = $data['_embedded'];
            unset($data['_embedded']);
        }

        return new static($client, $data, $links, $embedded);
    }

    /**
     * @param HalClient $client
     * @param array     $properties
     * @param array     $links
     * @param array     $embedded
     */
    public function __construct(HalClient $client, array $properties = [], array $links = [], array $embedded = [])
    {
        $this->properties = $properties;

        $this->createLinks($client, $links);
        $this->createEmbedded($client, $embedded);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @param      $name
     * @param null $default
     *
     * @return mixed|null
     */
    public function getProperty($name, $default = null)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        return $default;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param string $rel The resource identifier
     *
     * @return static[]
     */
    public function getResources($rel)
    {
        if (isset($this->embedded[$rel])) {
            return $this->embedded[$rel];
        }

        return [];
    }

    /**
     * @return static[][]
     */
    public function getAllResources()
    {
        return $this->embedded;
    }

    /**
     * @param string $rel The resource identifier
     *
     * @return static|null
     */
    public function getFirstResource($rel)
    {
        $resources = $this->getResources($rel);
        if ($resources) {
            return $resources[0];
        }

        return null;
    }

    /**
     * @param string $rel The resource identifier
     *
     * @return HalLink|null
     */
    public function getLink($rel)
    {
        if (isset($this->links[$rel])) {
            return $this->links[$rel];
        }

        return null;
    }

    /**
     * @param array $options
     *
     * @return null|HalResource
     */
    public function get(array $options = [])
    {
        return $this->getLink('self')->get([], $options);
    }

    /**
     * @param array     $links
     * @param HalClient $client
     */
    private function createLinks(HalClient $client, array $links)
    {
        foreach ($links as $rel => $link) {
            if ($link instanceof HalLink) {
                $this->links[$rel] = $link;
                continue;
            }

            $this->links[$rel] = new HalLink($client, $link['href'], $link);
        }
    }

    /**
     * @param HalClient $client
     * @param array     $relations
     */
    private function createEmbedded(HalClient $client, array $relations)
    {
        foreach ($relations as $name => $resources) {
            if (array_values($resources) !== $resources) {
                $resources = [$resources];
            }

            foreach ($resources as $index => $resource) {
                $this->embedded[$name][$index] = static::fromArray($client, $resource);
            }
        }
    }
}
