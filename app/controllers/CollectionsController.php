<?php

/**
 * CollectionsController is used to manage Collection directly as a resource.
 */

class CollectionsController extends \BaseController {

  /**
   * Create instance.
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Create a collection for a User.
   *
   * @param  integer $userId         ID of the user.
   * @param  string  $collectionName Name for the new collection.
   * @return boolean True if succeeded, false otherwise.
   */
  public function createUserCollection($userId, $collectionName)
  {
    $collection = new Collection;
    $collection->name = $collectionName;
    $collection->slug = $this->slugify($collection->name);
    $collection->user_id = $userId;

    // Status is currently set to public by default.
    $collection->status = 1;

    $saved = $collection->save();
    if (!$saved) return false;

    return true;
  }

}