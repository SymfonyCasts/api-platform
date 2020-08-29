# Reproducing metadata cache bug

## Setup

```
git clone git@github.com:SymfonyCasts/api-platform.git
git checkout -b export-cache-bug
composer install
symfony serve -d

# docker env vars will automatically be exposed to symfony server
# so no need to configure DATABASE_URL
docker-composer up -d

symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```

## Reproducing

A) Go to https://localhost:8000/api and find the
   `cheese:133b9e380534afbb99241170aac664f3-cheese:read` schema
   near the bottom. It will have a `title` property with no metadata.

B) In `src/Dto/CheeseListingOutput`, make this change:

```diff
- public $title;
+ public string $title;
```

C) Refresh: the model will remain unchanged: the `title` property in
   the schema will still exist, but will still have no metadata.

D) Trigger a cache refresh by making the following change in
   `src/Dto/CheeseListingOutput`

```diff
    /**
-      * @Groups({"cheese:read"})
+      * @Groups({"cheese:foo"})
     */
    public string $title;
```

E) Refresh: the `title` property in the schema WILL now be aware that
   the `title` property is a `string. The `string` type wasn't noticed
   until something else triggered a metadata reload.

