<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order;

use RuntimeException;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\Document\AbstractDocument;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Exception;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Hal;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Operation;

class OrderOperation extends Operation\AbstractBulkOperation
{
    /**
     * Operation types
     */
    public const TYPE_ACCEPT           = 'accept';
    public const TYPE_CANCEL           = 'cancel';
    public const TYPE_REFUSE           = 'refuse';
    public const TYPE_SHIP             = 'ship';
    public const TYPE_REFUND           = 'refund';
    public const TYPE_ACKNOWLEDGE      = 'acknowledge';
    public const TYPE_UNACKNOWLEDGE    = 'unacknowledge';
    public const TYPE_UPLOAD_DOCUMENTS = 'upload-documents';

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
        self::TYPE_UPLOAD_DOCUMENTS,
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
     * @param string $reference                                Order reference
     * @param string $channelName                              Channel to notify
     * @param string $carrier                                  Optional carrier name
     * @param string $trackingNumber                           Optional tracking number
     * @param string $trackingLink                             Optional tracking link
     * @param array<array{id: int, quantity: int}> $items      Optional items
     *
     * @return OrderOperation
     *
     * @throws Exception\InvalidArgumentException
     */
    public function ship(
        $reference,
        $channelName,
        $carrier = '',
        $trackingNumber = '',
        $trackingLink = '',
        $items = []
    )
    {
        $this->addOperation(
            $reference,
            $channelName,
            self::TYPE_SHIP,
            compact('carrier', 'trackingNumber', 'trackingLink', 'items')
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
     * @param string                    $reference The channel's order reference
     * @param string                    $channelName The channel's name
     * @param Document\AbstractDocument $document
     *
     * @return OrderOperation
     *
     * @throws Exception\InvalidArgumentException
     */
    public function uploadDocument($reference, $channelName, Document\AbstractDocument $document): self
    {
        $this->addOperation(
            $reference,
            $channelName,
            self::TYPE_UPLOAD_DOCUMENTS,
            ['document' => $document]
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
            $this->populateRequests($type, $link, $requests);
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

    private function populateRequests($type, Hal\HalLink $link, \ArrayAccess $requests): void
    {
        // Upload documents require dedicated processing because of file upload specificities
        if (self::TYPE_UPLOAD_DOCUMENTS === $type) {
            $this->populateRequestsForUploadDocuments($link, $requests);
            return;
        }

        $this->eachBatch(
            function (array $chunk) use ($type, $link, &$requests) {
                $requests[] = $link->createRequest(
                    'POST',
                    ['operation' => $type],
                    ['order' => $chunk]
                );
            },
            $type
        );
    }

    /**
     * Create requests for upload documents operation. We batch request by 20
     * to not send too many files at once.
     *
     * @param Hal\HalLink $link
     * @param array       $requests
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    private function populateRequestsForUploadDocuments(Hal\HalLink $link, \ArrayAccess $requests)
    {
        $type = self::TYPE_UPLOAD_DOCUMENTS;

        foreach (array_chunk($this->getOperations($type), 20) as $batch) {
            $body   = [];
            $orders = [];

            foreach ($batch as $operation) {
                /** @var AbstractDocument $document */
                $document = $operation['document'];

                $resource = fopen($document->getPath(), 'rb');

                if (false === $resource) {
                    throw new RuntimeException(
                        sprintf('Unable to read "%s"', $document->getPath())
                    );
                }

                $body[] = [
                    'name'     => 'files[]',
                    'contents' => $resource,
                ];

                $orders[] = [
                    'reference'   => $operation['reference'],
                    'channelName' => $operation['channelName'],
                    'documents'   => [
                        ['type' => $document->getType()],
                    ],
                ];
            }

            $body[] = [
                'name'     => 'body',
                'contents' => json_encode(['order' => $orders]),
            ];

            $requests[] = $link->createRequest(
                'POST',
                ['operation' => $type],
                $body,
                ['Content-Type' => 'multipart/form-data']
            );
        }
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
