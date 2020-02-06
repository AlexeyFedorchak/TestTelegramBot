<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    $router->resource('custom/users', UserController::class);
    $router->resource('custom/categories', CategoriesController::class);
    $router->resource('custom/feedbacks', FeedbackController::class);
});
