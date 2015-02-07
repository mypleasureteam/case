<?php

namespace Mypleasure\Services\Providers;

use Tag;
use User;
use Video;
use Config;
use Collection;
use TagsController;
use AuthController;
use UsersController;
use VideosController;
use CollectionsController;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Mypleasure\Services\Url\UrlSanitizer;
use Mypleasure\Services\Validation\User\UserAuthValidator;
use Mypleasure\Services\Validation\User\UserCreateValidator;
use Mypleasure\Services\Validation\User\UserDestroyValidator;
use Mypleasure\Services\Validation\User\UserUpdateEmailValidator;
use Mypleasure\Services\Validation\User\UserUpdatePasswordValidator;

/**
 * UserServiceProvider registers all users-related services.
 * It leverages Laravel's IoC mechanism to deliver the expected
 * dependencies injections in selected classes' instances
 * whenever we invoke them throughout our app.
 */

class UserServiceProvider extends ServiceProvider {

  public function register()
  {
    $this->bindValidators();
    $this->bindControllers();
  }

  /**
   * Bind calls to diverse user form validators.
   *
   * @return void
   */
  protected function bindValidators()
  {
    $this->app->bind('UserAuthValidator', function($app) {
      return new UserAuthValidator(Validator::getFacadeRoot());
    });

    $this->app->bind('UserCreateValidator', function($app) {
      return new UserCreateValidator(Validator::getFacadeRoot());
    });

    $this->app->bind('UserDestroyValidator', function($app) {
      return new UserDestroyValidator(Validator::getFacadeRoot());
    });

    $this->app->bind('UserUpdateEmailValidator', function($app) {
      return new UserUpdateEmailValidator(Validator::getFacadeRoot());
    });

    $this->app->bind('UserUpdatePasswordValidator', function($app) {
      return new UserUpdatePasswordValidator(Validator::getFacadeRoot());
    });
  }

  /**
   * Bind calls to diverse controllers.
   *
   * @return void
   */
  protected function bindControllers()
  {
    $this->app->bind('CollectionsController', function($app) {
      return new CollectionsController;
    });

    $this->app->bind('VideosController', function($app) {
      return new VideosController(new UrlSanitizer);
    });

    $this->app->bind('TagsController', function($app) {
      return new TagsController(new Tag, new Video);
    });

    $this->app->bind('UsersController', function($app) {
      return new UsersController(
        new User,
        array(
          'create' => $app->make('UserCreateValidator'),
          'destroy' => $app->make('UserDestroyValidator'),
          'updateEmail' => $app->make('UserUpdateEmailValidator'),
          'updatePassword' => $app->make('UserUpdatePasswordValidator')
        ),
        array(
          'collection' => $app->make('CollectionsController'),
          'video' => $app->make('VideosController'),
          'tag' => $app->make('TagsController')
        )
      );
    });

    $this->app->bind('AuthController', function($app) {
      return new AuthController(
        $app->make('UserAuthValidator'),
        Config::get('app.throttling_max_attempts'),
        Config::get('app.throttling_retention_time')
      );
    });
  }

}
