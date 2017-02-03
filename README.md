## Cheap routes

The idea is to find all possible routes from one point to another (by plain, by train, by car, etc.). Also very important part is to show duration and times (sort options?). Let's start with 1 specific route and 1 company (for example Amsterdam - Zaporizhia and Flypgs company)

A little bit about implementation:
* separate http client without any knowledge about sites/companies/providers...
* for the beginning we can start with configuration in files (different sites have different names/autosuggests/etc.)
* we should have interfaces and basic implementations for request/response/provider(airline/site/...)
* as a storage let's use mongodb because of the easiest way to update scheme (schemeless)
* later for a search system let's use elasticsearch (or kibana?) - for the beginning we can use symfony io to display results


## How does it work?

$ composer install

$ docker-compose up

$ php bin/console.php