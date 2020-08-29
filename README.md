# Nothing to see

## Setup

```
git clone git@github.com:SymfonyCasts/api-platform.git
cd api-platform
git checkout -b expose-extra-info origin/expose-extra-info
composer install
symfony serve -d

# docker env vars will automatically be exposed to symfony server
# so no need to configure DATABASE_URL
docker-compose up -d

symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```

## Testing

Run:

```
curl -X POST "https://127.0.0.1:9030/api/cheeses" -H "accept: application/ld+json" -H "Content-Type: application/ld+json" -d "{\"price\":\"apple\"}"
```

Notice the `hydra:description` is:

> The type of the \"price\" attribute for class \"App\\Dto\\CheeseListingInput\" must be one of \"int\" (\"string\" given).

