<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Resource;

class PaginationCriteria
{
    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var array
     */
    private $filters;

    /**
     * @param array $criteria
     */
    public function __construct($criteria = [])
    {
        $this->page    = (int) (isset($criteria['page']) ? $criteria['page'] : 1);
        $this->limit   = (int) (isset($criteria['limit']) ? $criteria['limit'] : AbstractDomainResource::PER_PAGE);
        $this->filters = (array) (isset($criteria['filters']) ? $criteria['filters'] : []);
    }

    /**
     * Current page to get
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Item per page to retrieve
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Filters to apply to query
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Convert criteria as ready to be added to URL
     *
     * @return array
     */
    public function getQueryParams()
    {
        $query = [
            'page'  => $this->page,
            'limit' => $this->limit,
        ];

        if ($this->filters) {
            foreach ($this->filters as $field => $values) {
                // The norm in the API for multiple values is to pass them comma separated
                if (is_array($values)) {
                    $query[$field] = implode(',', $values);
                    continue;
                }

                // Format date in ISO 8601
                if ($values instanceof \DateTimeInterface) {
                    $query[$field] = $values->format('c');
                    continue;
                }

                $query[$field] = (string) $values;
            }
        }

        return $query;
    }
}
