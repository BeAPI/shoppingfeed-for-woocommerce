# Development

If you want to contribute, you can fork us on github and submit a pull request with your modifications.
Please review the [contributing guideline](../../CONTRIBUTING.md) before submitting issues or PR.
You should refer to our [coding standard rules](coding-standards.md), as any code not following these rules
will not be accepted.

## Tools

### Docker

We provide with this repository a docker config that can help you start developing on the project.  
You need to install docker and docker-compose in order to use it.  
  
Once the installation is done, in your project root, where you have cloned the repo, run these commands :

1. Build the image
```bash
docker-compose build
```
2. Install project dependencies
```bash
docker-compose run sf-php-sdk-dev composer install --dev
```

## Code checks

To help you test your code against our requirements, there is a composer test script configured :
```bash
docker-compose run sf-php-sdk-dev composer test 
```

The script will run `phpunit` and `phpcs`.
Reports for the runs can be found in `build/*`.
