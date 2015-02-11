<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\Collection as IlluminateCollection;

/**
 * id             {integer}
 * username       {string}
 * password       {string}
 * email          {string}
 * role_id        {integer|foreign:Role}
 * status         {integer}
 * remember_token {string}
 * created_at     {timestamp}
 * updated_at     {timestamp}
 * deleted_at     {timestamp}
 */
class User extends Eloquent implements UserInterface, RemindableInterface {

  use UserTrait, RemindableTrait;

  /**
   * The suffix used when crafting a dummy email.
   *
   * @const string
   */
  public static $EMAIL_PLACEHOLDER_SUFFIX = '.no.email.provided@mypleasu.re';

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'users';

  /**
   * The attributes excluded from the model's JSON form.
   *
   * @var array
   */
  protected $hidden = array('password', 'remember_token');

  /**
   * The mass-assignable attributes in this model.
   *
   * @var array
   */
  protected $fillable = array('username', 'password', 'email', 'status', 'role_id', 'created_at', 'updated_at');

  /**
   * Start watching UserObserver on model's boot sequence.
   */
  public static function boot()
  {
    parent::boot();
    self::observe(new UserObserver);
  }

  /**
   * Relation with Role model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function role()
  {
    return $this->belongsTo('Role');
  }

  /**
   * Relation with Collection model.
   *
   * @return Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function collections()
  {
    return $this->hasMany('Collection');
  }

  /**
   * Returns an Illuminate collection of Video instances linked to this User.
   *
   * @return Illuminate\Database\Eloquent\Collection
   */
  public function videos()
  {
    // First, the Illuminate\Support\Collection's of "Collection" models.
    $collectionsList = $this->collections;

    // Prepare results array.
    $videos = new IlluminateCollection;

    // Traverse Illuminate\Support\Collection's to get Collection model instances,
    // and access the Illuminate\Support\Collection list of videos herein.
    $collectionsList->each(function($collectionModel) use (&$videos) {
      $videos = $videos->merge($collectionModel->videos);
    });

    return $videos;
  }

  /**
   * Whether user has a placeholder email.
   *
   * @return boolean
   */
  public function hasPlaceholderEmail()
  {
    return stripos($this->email, self::$EMAIL_PLACEHOLDER_SUFFIX);
  }

  /**
   * Use given id to find out if user has video or not.
   *
   * @param  integer  $id The ID of video to check.
   * @return boolean  True if user has the video, false otherwise.
   */
  public function hasVideo($id)
  {
    $hasVideo = false;

    $this->collections->each(function($collection) use (&$hasVideo, &$id) {
      $collection->videos->each(function($video) use (&$hasVideo, &$id) {
        if ($video->id === $id) {
          $hasVideo = true;
        }
      });
    });

    return $hasVideo;
  }

  /**
   * Use given hash to find out if user has already
   * curated a particular video.
   *
   * @param  string  $hash Hash generated from the URL-encoded UTF8 transcoded name of the video.
   * @return boolean       True if user already has the video, false otherwise.
   */
  public function hasVideoFromHash($hash)
  {
    $hasVideo = false;

    $this->collections->each(function($collection) use (&$hasVideo, &$hash) {
      $collection->videos->each(function($video) use (&$hasVideo, &$hash) {
        if ($video->hash === $hash) {
          $hasVideo = true;
        }
      });
    });

    return $hasVideo;
  }

}
