# Reproducing output readableLink bug

## Setup

```
git clone git@github.com:SymfonyCasts/api-platform.git
cd api-platform
git checkout -b embedded-output-iri-embedded-bug origin/embedded-output-iri-embedded-bug
composer install
symfony serve -d

# docker env vars will automatically be exposed to symfony server
# so no need to configure DATABASE_URL
docker-compose up -d

symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```

## Reproducing

A) Go to https://localhost:8000/api/users.jsonld and notice
   that the `cheeseListings` property is an array of
   *embedded objects*, even though there are not fields to
   embed. The proper behavior should be that this is an array
   of IRI strings.

B) The reason behind the bad behavior is that CheeseListing has
    an output class (`CheeseListingOutput`). But, when the
    property metadata for the `User.cheeseListings` is calculated,
    the serialization groups are read from `CheeseListing` instead
    of `CheeseListingOutput`. This causes the `readableLink` to be
    set to `false` because it *seems* like some properties on
    `CheeseListing` will be serialized. But at runtime, when the
    correct `CheeseListingOutput` is used, nothing is actually
    serialized. 
