# This package provides helpful classes for basic content, repositories and APIs

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