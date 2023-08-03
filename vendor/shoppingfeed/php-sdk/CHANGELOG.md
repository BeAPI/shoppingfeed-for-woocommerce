v0.3.1
======

### Added

- Add accessor for itemsReferenceAliases #87

v0.3.0
======

### Added

- Add ticket API access #83 
- Removing git flow use for dev workflow #83 

### BC-Break

Retrieval of ticket after sending operation on order as changed, see [order documentation](https://github.com/shoppingflux/php-sdk/blob/master/docs/manual/resources/order.md#operations) for new implementation.

v0.2.7
======

### Added

- `OrderOperation` : Adding access to refund operation API #81

### Removed

- On the refuse operation, the `$refund` parameter has been removed has it is not used by the API anymore

v0.2.6
======

### Added

- `OrderItem` : Adding TaxAmount retrieval #74


v0.2.5
======

### Added

- `PricingResource` : access to catalog pricing API #69

### Fixed

- Readme fixes #65

v0.2.4
======

### Added

- `SessionResource::getId()` : provides the account id associated to the token #63
- `SessionResource::getRoles()` : provides an array of named roles associated to the token #63
- `SessionResource::getLanguageTag()` : provides the language tag associate to the token #63
- `AbstractDomainResource::getOne($id)` : allow to directly get a resource for the domain #63
- `HalLink::withAddedHref(string $path)` : Allow to creates new instance of link with concatenated path #63
- Allow to add platform information to SDK user agent #62

### Fixed

- `InventoryDomain::getByReference()` was not checking for NULL resource #63
- `Client::VERSION` mismatch with release version #62


v0.2.3
======

- Add store channel resource token getter [#59](https://github.com/shoppingflux/php-sdk/pull/59)

v0.2.2-beta.1
=============

- Remove Guzzle library dependency [#50](https://github.com/shoppingflux/php-sdk/issues/50)
- Add order collection filter documentation [#55](https://github.com/shoppingflux/php-sdk/issues/55)
- Fix behaviour when no inventory is updated when doing an inventory update [#52](https://github.com/shoppingflux/php-sdk/issues/52)
- Fix order acknowledge date handling [#57](https://github.com/shoppingflux/php-sdk/issues/57)

v0.2.1-beta.1
=============

- Add session token getter [#45](https://github.com/shoppingflux/php-sdk/pull/45)
- Add order item read from order [#47](https://github.com/shoppingflux/php-sdk/pull/47)
- Add channel read from order [#47](https://github.com/shoppingflux/php-sdk/pull/47)

v0.2.0-beta.1
=============

- Add authentication and session creation
- Add store retrieval from session
- Add inventory fetch for a reference [#6](https://github.com/shoppingflux/php-sdk/pull/6)
- Add inventory collection fetch [#6](https://github.com/shoppingflux/php-sdk/pull/6)
- Add inventory update operation [#36](https://github.com/shoppingflux/php-sdk/pull/36)
- Add order status update operation for : acknowledgment, unacknowledgment, acceptance, cancellation, refusal and shipment [#35](https://github.com/shoppingflux/php-sdk/pull/35)
- Add fetch order collection [#39](https://github.com/shoppingflux/php-sdk/pull/39)
- Add status filtering to order collection fetch [#39](https://github.com/shoppingflux/php-sdk/pull/39)


Versioning is based on [semver](https://semver.org/) specification.
 