# Pricing operations

### Read pricings

Accessing the pricing API can be done using the store resource

```php
<?php
$pricingApi = $session->getMainStore()->getPricingApi();
```

Find an pricing item by a product's reference (sku):

```php
<?php
/** @var \ShoppingFeed\Sdk\Api\Catalog\PricingDomain $pricingApi */
$item = $pricingApi->getByReference('AZUR103-XL');
// Access the quantity as integer
$item->getPrice();
// Access the reference
$item->getReference();
// Get last modification date time
$item->getUpdatedAt()->format('c');
```

Get a particular page of pricing

```php
<?php
/** @var \ShoppingFeed\Sdk\Api\Catalog\PricingDomain $pricingApi */
$criteria = [
  'page' => 1,  
  'limit' => 10,  
];
foreach ($pricingApi->getPage($criteria) as $pricing) {
	echo $pricing->getPrice() . PHP_EOL;
}
```

Or iterate over all pricing of your catalog

```php
<?php
/** @var \ShoppingFeed\Sdk\Api\Catalog\PricingDomain $pricingApi */
foreach ($pricingApi->getAll() as $pricing) {
	echo $pricing->getPrice() . PHP_EOL;
}
```

### Update pricing

```php
<?php
/** @var \ShoppingFeed\Sdk\Api\Catalog\PricingDomain $pricingApi */
$pricingUpdate = new \ShoppingFeed\Sdk\Api\Catalog\PricingUpdate();
$pricingUpdate
    ->add('ref1', 1.25)
    ->add('ref2', 2.10);
$pricingApi->execute($pricingUpdate);
```

The collection object holds updated resources

```php
<?php
/** @var \ShoppingFeed\Sdk\Api\Catalog\PricingResource[] $collection */
// Retrieve the content of resources
foreach ($collection as $pricing) {
	echo $pricing->getId() . PHP_EOL;
	echo $pricing->getUpdatedAt()->format('c') . PHP_EOL;
}
```
