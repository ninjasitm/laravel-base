# This package provides helpful classes for basic content, repositories and APIs

## Repository Remotes

This project uses GitLab as the primary origin and a GitHub mirror remote for pull request workflows.

```
git remote -v
git remote add github https://github.com/ninjasitm/laravel-base.git
```

Use the `github` remote when running `gh` commands against GitHub PRs (for example `https://github.com/ninjasitm/laravel-base/pull/4`).

# Base Content
This package provides support for custom repositories, app helpers and useful traits to help improve development

You may publish config and migrations using the following commands:

```
php artisan vendor:publish  --tag=nitm-content
php artisan vendor:publish  --tag=nitm-content-config
php artisan vendor:publish  --tag=nitm-content-migrations
```
# API

This package provides support to help improve api development

You may publish config using the following commands:

```
php artisan vendor:publish  --tag=nitm-api-config
```

To Publish the classes and traits ndeeded
```
php artisan vendor:publish  --tag=nitm-api
```

To Publish the infyom templates
```
php artisan vendor:publish  --tag=nitm-api-infyom
```
## Swagger
Be sure to add the following to the top of your base API controller:

```
/**
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="Site Title",
 *     version="1.0",
 *     description="Site description",
 *     @SWG\Contact(
 *         email="xyz@xyz.com"
 *     )
 *   )
 * )
 */
 class Controller {
 ```
## Dependedncies
 - `infyom/laravel-generator` for API and model generation support

# Using numeric IDs, Hashids or UUIDs

You may use the raw ids, hashed ids or UUIDs when serializing data for API responses

## Pros and Cons
|Item | Pro | Con |
|-----|-----|-----|
|IDs|Easy to use and require no changes|Guessable and can lead to DDoS attacks using sequential IDs|
|UUIDs|Provides truly unique IDs|Long and can create unwieldy Ids|
|HashIds|Based on IDs and simply mask the real ID|May introduce complexity when converting between IDs and HashIds|

## Using UUIDs
Attach the `Nitm\Content\Traits\SetUuid` trait to your model. You may alsowant to extends your Http Resources from the `Nitm\Content\Http\Resources\BaseResource` class.

## Using HashIds
Attach the `Nitm\Content\Traits\SupportsHashIds` trait to your model. You may alsowant to extends your Http Resources from the `Nitm\Content\Http\Resources\BaseResource` class.

Additionally you may use the `resolveRouteBindingUsingHashId` function in your `resolveRouteBinding` method:

```
public function resolveRouteBinding($value, $field = null) {
    return $this->resolveRouteBindingUsingHashId($value, $field);
}
```