# Error - Internal Server Error

Call to undefined method App\Http\Controllers\Admin\RoleController::create()

PHP 8.3.6
Laravel 13.7.0
127.0.0.1:8000

## Stack Trace

0 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
1 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:269
2 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:215
3 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
4 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
5 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:52
6 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
7 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestForgery.php:104
8 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
9 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
10 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
11 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
12 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
13 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
14 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
15 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
16 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
17 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
18 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
19 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
20 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
21 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
22 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
23 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
24 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
25 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
26 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
27 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
28 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
29 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
30 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
31 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
32 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
33 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
34 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
35 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
36 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
37 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
38 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
39 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
40 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
41 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:28
42 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
43 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
44 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
45 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
46 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
47 - public/index.php:20
48 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23


## Request

GET /admin/roles/create

## Headers

* **host**: 127.0.0.1:8000
* **connection**: keep-alive
* **sec-ch-ua**: "Google Chrome";v="147", "Not.A/Brand";v="8", "Chromium";v="147"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://127.0.0.1:8000/admin/roles
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: id,en-US;q=0.9,en;q=0.8
* **cookie**: XSRF-TOKEN=eyJpdiI6IkVnZDlOM3FYMUhIOU9GQmE1SWp3aEE9PSIsInZhbHVlIjoiZXVVM2k2WUQzNWlSampEU0tueDlhQ2NwVnlrM0ZVbWpmZE9La1NRYzhyVDdCZnp1SVlTUDgxTy9GWXJDTkltaWdDNmJlcFFtUDNiejFvV2p3eWlSMGovbDZGM1lzSGdQMjlDSjFWb1N2MWd6cnV2S085ZXJkZmJ2d3pSSnFlV00iLCJtYWMiOiJkN2ViNTQwMTkyMjY0MTA3YTg4MGE4ZTQ0M2JmZmQxMzcxOWU0MzdkNjZlNTMwYmZlNDdlNmEzNWY3Nzc1YmQ2IiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6IkxaK2RYZ0MyZU9UMDRqRkJXOW1DSGc9PSIsInZhbHVlIjoiQ0lrbzhmRW1sSnZoUVFRdWJkR0NZRDUrUXAzWVFhS0k4dmY4NzlPeU1QT0x0TFA3YytuV0NmeHEwcURScFNrQlZaTUsxVzhVcm83dFAvZUJrN1VhNXdlLzd0aURSRFU2bEg2WndiUnlGQndhbnpYSDJuUGlZcU8rM1JyUUh1OHMiLCJtYWMiOiI1YjFjNjc4YzU1Y2E1NmZiMmNjYTBhZjQ0NmQ1OTUyODcyYWRjMzVhYjVjYjk5MmNjNTgyNmI4ODQwOWQ1NWQxIiwidGFnIjoiIn0%3D

## Route Context

controller: App\Http\Controllers\Admin\RoleController@create
route name: admin.roles.create
middleware: web

## Route Parameters

No route parameter data available.

## Database Queries

* mysql - select * from `sessions` where `id` = 'vNHJ0rIFEGYGxVWaf6z8K1NTsxCDumHiH9QsNo8E' limit 1 (8.35 ms)
* mysql - select * from `users` where `id` = 1 limit 1 (2.29 ms)

# Error - Internal Server Error

Call to undefined method App\Http\Controllers\Admin\RoleController::edit()

PHP 8.3.6
Laravel 13.7.0
127.0.0.1:8000

## Stack Trace

0 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
1 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:269
2 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:215
3 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
4 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
5 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:52
6 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
7 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestForgery.php:104
8 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
9 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
10 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
11 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
12 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
13 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
14 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
15 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
16 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
17 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
18 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
19 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
20 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
21 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
22 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
23 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
24 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
25 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
26 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
27 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
28 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
29 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
30 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
31 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
32 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
33 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
34 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
35 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
36 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
37 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
38 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
39 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
40 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
41 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:28
42 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
43 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
44 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
45 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
46 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
47 - public/index.php:20
48 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23


## Request

GET /admin/roles/1/edit

## Headers

* **host**: 127.0.0.1:8000
* **connection**: keep-alive
* **sec-ch-ua**: "Google Chrome";v="147", "Not.A/Brand";v="8", "Chromium";v="147"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://127.0.0.1:8000/admin/roles
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: id,en-US;q=0.9,en;q=0.8
* **cookie**: XSRF-TOKEN=eyJpdiI6IlhydFpERkF4WGEyWC9WcDE2WTZUcEE9PSIsInZhbHVlIjoiU3pVVUtZSGR0SFJDMnJNaVJjVTZqTkFBcFYvK0NPcnZ1UkVHRWRPVU0vVWNENGZFN1dOc1ZLY1pWNHZvc3g3YzcyMlBkeGd3Ni84bTVia0gvMWFWM1RiamVnS05pU3FySGhKWWRiUkpveitVUWJQamVHUTgyck9aR0tSQ3lVOWQiLCJtYWMiOiIyYTYyMmU0NzIyNzIxNjJhMjVjOTBkMzZjMDIwZDgyYzBmNTljYzA3YjFmZDdiMDZjODlhODYzMmI1NTFkZjc0IiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6IitERkx4M3dmTVNwQjlWTjUrTDI2RHc9PSIsInZhbHVlIjoiZHZ5RFJDazM3c0w5bmxFcnpKaG84VzFqT3lQb3lLb2tlWEJsRXI0UTdIY2tmTkVJcmZrVnNNRU5INHNGaEswdUk1RFVLVDJvcWtHMFpUMWRBVmY1enN4SjNDTFg3OUJrQUNsSTNzMFNaZkdVbmRJUktxakV1RHhxOGhHZ3J6ai8iLCJtYWMiOiIzYjdhNDRmNmVjMTE5NzQ3ZDFiNjJlNzFhODNiODZjMTkzNzYxZjI3ZWYxZGY1ZjIyYjAxNDk4MGE3ZTlmMmMyIiwidGFnIjoiIn0%3D

## Route Context

controller: App\Http\Controllers\Admin\RoleController@edit
route name: admin.roles.edit
middleware: web

## Route Parameters

{
    "role": "1"
}

## Database Queries

* mysql - select * from `sessions` where `id` = 'vNHJ0rIFEGYGxVWaf6z8K1NTsxCDumHiH9QsNo8E' limit 1 (7.5 ms)
* mysql - select * from `users` where `id` = 1 limit 1 (1.35 ms)
