<?php
namespace ShoppingFeed\Sdk\Api\Order;

use ShoppingFeed\Sdk\Api;
use ShoppingFeed\Sdk\Hal;
use ShoppingFeed\Sdk\Operation;
use ShoppingFeed\Sdk\Exception;

class OrderOperation extends Operation\AbstractBulkOperation
{
    /**
     * Operation types
     */
    const TYPE_ACCEPT        = 'accept';
    const TYPE_CANCEL        = 'cancel';
    const TYPE_REFUSE        = 'refuse';
    const TYPE_SHIP          = 'ship';
    const TYPE_REFUND        = 'refund';
    const TYPE_ACKNOWLEDGE   = 'acknowledge';
    const TYPE_UNACKNOWLEDGE = 'unacknowledge';

    /**
     * @var array
     */
    private $allowedOperationTypes = [
        self::TYPE_ACCEPT,
        self::TYPE_CANCEL,
        self::TYPE_REFUSE,
        self::TYPE_SHIP,
        self::TYPE_REFUND,
        self::TYPE_ACKNOWLEDGE,
        self::TYPE_UNACKNOWLEDGE,
    ];

    /**
     * @var array
     */
    protected $operations = [];

    /**
     * Notify market place of order acceptance
     *
     * @param string $reference   Order reference
     * @param string $channelName Channel to notify
     * @param string $reason      Optional reason of acceptance
     *
     * @return OrderOperation
     *
     * @throws Exception\InvalidArgumentException
     */
    public function accept($reference, $channelName, $reason = '')
    {
        $this->addOperation(
            $reference,
            $channelName,
            self::TYPE_ACCEPT,
            compact('reason')
        );

        return $this;
    }

    /**
     * Notify market place of order cancellation
     *
     * @param string $reference   Order reference
     * @param string $channelName Channel to notify
     * @param string $reason      Optional reason of cancellation
     *
     * @return OrderOperation
     *
     * @throws Exception\InvalidArgumentException
     */
    public function cancel($reference, $channelName, $reason = '')
    {
        $this->addOperation(
            $reference,
            $channelName,
            self::TYPE_CANCEL,
            compact('reason')
        );

        return $this;
    }

    /**
     * Notify market place of order shipment sent
     *
     * @param string $reference      Order reference
     * @param string $channelName    Channel to notify
     * @param string $carrier        Optional carrier name
     * @param string $trackingNumber Optional tracking number
     * @param string $trackingLink   Optional tracking link
     *
     * @return OrderOperation
     *
     * @throws Exception\InvalidArgumentException
     */
    public function ship($reference, $channelName, $carrier = '', $trackingNumber = '', $trackingLink = '')
    {
        $this->addOperation(
            $reference,
            $channelName,
            self::TYPE_SHIP,
            compact('carrier', 'trackingNumber', 'trackingLink')
        );

        return $this;
    }

    /**
     * Notify market place of order refusal
     *
     * @param string $reference   Order reference
     * @param string $channelName Channel to notify
     *
     * @return OrderOperation
     *
     * @throws Exception\InvalidArgumentException
     */
    public function refuse($reference, $channelName)
    {
        $this->addOperation(
            $reference,
            $channelName,
            self::TYPE_REFUSE
        );

        return $this;
    }

    /**
     * Acknowledge order reception
     *
     * @param string $reference
     * @param string $channelName
     * @param string $status
     * @param string $storeReference
     * @param string $message
     *
     * @return OrderOperation
     *
     * @throws Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function acknowledge($reference, $channelName, $storeReference = '', $status = 'success', $message = '')
    {
        $acknowledgedAt = date_create()->format('c');

        $this->addOperation(
            $reference,
            $channelName,
            self::TYPE_ACKNOWLEDGE,
            compact('status', 'storeReference', 'acknowledgedAt', 'message')
        );

        return $this;
    }

    /**
     * Unacknowledge order reception
     *
     * @param string $reference   The channel's order reference
     * @param string $channelName The channel's name
     *
     * @return OrderOperation
     *
     * @throws Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function unacknowledge($reference, $channelName)
    {
        $this->addOperation(
            $reference,
            $channelName,
            self::TYPE_UNACKNOWLEDGE
        );

        return $this;
    }

    /**
     * Execute all declared operations
     *
     * @param Hal\HalLink $link
     *
     * @return Api\Order\OrderOperationResult
     */
    public function execute(Hal\HalLink $link)
    {
        $requests  = new \ArrayObject();
        $resources = new \ArrayObject();

        foreach ($this->allowedOperationTypes as $type) {
            $this->eachBatch(
                $this->createRequestGenerator($type, $link, $requests),
                $type
            );
        }

        $link->batchSend(
            $requests->getArrayCopy(),
            function (Hal\HalResource $batch) use ($resources) {
                $resources->append($batch);
            },
            null,
            [],
            $this->getPoolSize()
        );

        return new Api\Order\OrderOperationResult(
            $resources->getArrayCopy()
        );
    }

    /**
     * Create request generation callback
     *
     * @param string      $type
     * @param Hal\HalLink $link
     * @param array       $requests
     *
     * @return \Closure
     */
    private function createRequestGenerator($type, Hal\HalLink $link, \ArrayAccess $requests)
    {
        return function (array $chunk) use ($type, $link, &$requests) {
            $requests[] = $link->createRequest(
                'POST',
                ['operation' => $type],
                ['order' => $chunk]
            );
        };
    }

    /**
     * Add operation to queue
     *
     * @param string $reference   Order reference
     * @param string $channelName Channel to notify
     * @param string $type        Type of operation
     * @param array  $data        Extra data to pass to operation call
     *
     * @throws Exception\InvalidArgumentException
     */
    public function addOperation($reference, $channelName, $type, $data = [])
    {
        if (! in_array($type, $this->allowedOperationTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Only %s operations are accepted',
                implode(', ', $this->allowedOperationTypes)
            ));
        }

        if (! isset($this->operations[$type])) {
            $this->operations[$type] = [];
        }

        $this->operations[$type][] = array_merge(compact('reference', 'channelName'), $data);
    }

    /**
     * Notify market place of order refund
     *
     * @param string $reference   Order reference
     * @param string $channelName Channel to notify
     * @param bool   $shipping    True to refund shipping costs
     * @param array  $products    Order item reference and quantities that will be refunded
     *
     * @return OrderOperation
     *
     * @throws Exception\InvalidArgumentException
     */
    public function refund($reference, $channelName, $shipping = true, $products = [])
    {
        $this->addOperation(
            $reference,
            $channelName,
            self::TYPE_REFUND,
            ['refund' => compact('shipping', 'products')]
        );

        return $this;
    }
}
