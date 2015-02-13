<?php

use Carbon\Carbon;
use GrahamCampbell\Throttle\Facades\Throttle;
use Mypleasure\Services\Validation\User\UserAuthValidator;
use Mypleasure\Services\Validation\User\UserCreateValidator;
use Mypleasure\Services\Validation\User\UserUpdateEmailValidator;
use Mypleasure\Services\Validation\User\UserUpdatePasswordValidator;

/**
 * UsersController manages all that relates to the User as a direct resource:
 * user creation, deletion, password and email edition.
 */

class UsersController extends \BaseController {

  /**
   * An instance of the User model passed via injection, to loosen dependencies and allow easier testing.
   *
   * @var User
   */
  protected $user;

  /**
   * An instance of the form validator for user creation.
   *
   * @var Mypleasure\Services\Validation\User\UserCreateValidator
   */
  protected $createValidator;

  /**
   * An instance of the form validator for user's email update.
   *
   * @var Mypleasure\Services\Validation\User\UserUpdateEmailValidator
   */
  protected $updateEmailValidator;

  /**
   * An instance of the form validator for user's password update.
   *
   * @var Mypleasure\Services\Validation\User\UserUpdatePasswordValidator
   */
  protected $updatePasswordValidator;

  /**
   * An instance of the form validator for user's destruction.
   *
   * @var Mypleasure\Services\Validation\User\UserDestroyValidator
   */
  protected $destroyValidator;

  /**
   * An instance of CollectionsController.
   *
   * @var CollectionsController
   */
  protected $collectionsController;

  /**
   * An instance of VideosController.
   *
   * @var VideosController
   */
  protected $videosController;

  /**
   * Create instance.
   *
   * @param User  $user        An instance of the User model passed via injection, to loosen dependencies and allow easier testing.
   * @param array $validators  An array containing instances of UserCreateValidator, UserUpdateEmailValidator, UserUpdatePasswordValidator, UserDestroyValidator.
   * @param array $controllers An array containing instances of CollectionsController and VideosController.
   */
  public function __construct(User $user, array $validators, array $controllers)
  {
    parent::__construct();
    $this->user = $user;
    $this->createValidator = $validators['create'];
    $this->updateEmailValidator = $validators['updateEmail'];
    $this->updatePasswordValidator = $validators['updatePassword'];
    $this->destroyValidator = $validators['destroy'];
    $this->collectionsController = $controllers['collection'];
    $this->videosController = $controllers['video'];
  }

  /**
   * Display user's profile index page.
   *
   * @return Illuminate\View\View
   */
  public function getProfile()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();
    return View::make('users.profile')->with('user', $user);
  }

  /**
   * Display "edit email" form.
   *
   * @return Illuminate\View\View
   */
  public function getEditEmail()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();
    return View::make('users.email')->with('user', $user);
  }

  /**
   * Display "edit password" form.
   *
   * @return Illuminate\View\View
   */
  public function getEditPassword()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();
    return View::make('users.password')->with('user', $user);
  }

  /**
   * Display "delete account" form
   *
   * @return Illuminate\View\View
   */
  public function getDelete()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();
    return View::make('users.delete')->with('user', $user);
  }

  /**
   * Store new User resource.
   * POST /register
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function store()
  {
    // Get ID for "curator" role.
    $defaultRole = Role::where('name', '=', 'curator')->first();

    // Get input (with default values, if needed).
    $input = array(
      'username' => Input::get('username', ''),
      'email' => Input::get('email', ''),
      'password' => Input::get('password', ''),
      'password_confirmation' => Input::get('password_confirmation', ''),
      'status' => 0
    );

    // Provide default email address if none was given.
    if (trim($input['email']) === '') {
      $input['email'] = $this->generateDefaultEmail($input['username']);
    }

    // Validate inputs.
    $user = $this->createValidator->with($input)->passes();

    // Redirect with errors messages, if validation is unsuccessful.
    if (!$user) {
      return Redirect::route('auth.register')
        ->withErrors($this->createValidator->errors())
        ->withInput(Input::except('password', 'password_confirmation'));
    }

    // Create new User resource.
    $user = $this->createUser($input, $defaultRole->id);

    // Redirect with error message, if save is unsuccessful.
    if (!$user) {
      return Redirect::to('register')
        ->with('message', 'There was an error while creating your account. Please try again.');
    }

    // Create and link user's default collection.
    $this->collectionsController->createUserCollection($user->id, 'default');

    // Redirect the now logged-in user profile page otherwise.
    Auth::attempt(array(
      'username' => $input['username'],
      'password' => $input['password']
    ));
    return Redirect::route('users.profile');
  }

  /**
   * Destroys the specified User.
   * POST /me/delete
   *
   * @param  integer $id The ID of the user to destroy.
   * @return Illuminate\Http\RedirectResponse
   */
  public function destroy()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    $password = Input::get('password', '');

    // Validate input.
    $valid = $this->destroyValidator->with(array('password' => $password))->passes();
    if (!$valid) {
      return Redirect::route('users.delete')
        ->withErrors($this->destroyValidator->errors());
    }

    $match = Hash::check($password, $user->getAuthPassword());
    if (!$match) {
      return Redirect::route('users.delete')
        ->with('message', 'Password does not match, please try again.');
    }

    // Manually destroy related collections and videos.
    $this->collectionsController->destroyCollections($user->collections);
    $this->videosController->destroyVideos($user->videos());

    // Log out and destroy user.
    Auth::logout();
    $user->delete();

    // Redirect to landing page.
    return Redirect::route('static.home');
  }

  /**
   * Update user's password.
   * POST /me/edit/password
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function updatePassword()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Set user.
    $user = Auth::user();

    // Extract form input.
    $currentPassword = trim(Input::get('current_password', ''));
    $newPassword = trim(Input::get('password', ''));
    $newPasswordConfirmation = trim(Input::get('password_confirmation', ''));

    // Attempt input validation.
    $valid = $this->updatePasswordValidator
                  ->with(array(
                      'current_password' => $currentPassword,
                      'password' => $newPassword,
                      'password_confirmation' => $newPasswordConfirmation
                    ))->passes();

    // Send back to form if validation fails.
    if (!$valid) return Redirect::route('users.password')
                  ->withErrors($this->updatePasswordValidator->errors());

    // Stop if current password is invalid.
    if (!Hash::check($currentPassword, $user->getAuthPassword())) {
      return Redirect::route('users.password')
        ->with('message', 'Your current password is invalid. Please try again.');
    }

    // Update, save, leave.
    $user->password = Hash::make($newPassword);
    $saved = $user->save();

    if (!$saved) {
      return Redirect::route('users.password')
        ->with('message', 'An error occured. Please try again.');
    }

    return Redirect::route('users.profile')->with('message', 'Password updated.');
  }

  /**
   * Update user's email.
   * POST /me/edit/email
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function updateEmail()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    $user = Auth::user();
    $newEmail = trim(Input::get('email', ''));

    // Case 1 of 4: input is similar to stored email. No changes.
    if ($newEmail === $user->email) {
      return Redirect::route('users.email')
        ->with('message', 'No changes were made to your email.');
    }

    // Case 2 of 4: input is empty, and user who didn't give an email before.
    if ($newEmail === '' && $user->hasPlaceholderEmail()) {
      return Redirect::route('users.email')
        ->with('message', 'No changes were made. Remember you can not to reset a forgotten password without an email.');
    }

    // Case 3 of 4: user removes stored email.
    if ($newEmail === '' && !$user->hasPlaceholderEmail()) {
      $user->email = $this->generateDefaultEmail($user->username);
      $saved = $user->save();

      if (!$saved) App::abort(500, 'Oops... something went wrong! Please try again later.');

      return Redirect::route('users.email')
        ->with('message', 'Your email is permanently removed from our database. But remember, you can not to reset a forgotten password without an email.');
    }

    // Case 4 of 4: email was not stored, and now user stores it.
    $valid = $this->updateEmailValidator->with(array('email' => $newEmail))->passes();
    if (!$valid) return Redirect::route('users.email')->withErrors($this->updateEmailValidator->errors());

    $user->email = $newEmail;
    $saved = $user->save();

    if (!$saved) if (!$saved) App::abort(500, 'Oops... something went wrong! Please try again later.');

    return Redirect::route('users.profile')->with('message', 'Email updated.');
  }

  /**
   * Generate default and fake email address based on provided seed.
   *
   * @param string  $seed   The seed value provided to build fake email upon.
   * @return  string  A fake and default email address.
   */
  protected function generateDefaultEmail($seed)
  {
    $userClass = get_class($this->user);
    return md5($seed) . $userClass::$EMAIL_PLACEHOLDER_SUFFIX;
  }

  /**
   * Create a new User resource and save it in database.
   *
   * @param  array    $input    The array of attributes to create User from.
   * @param  integer  $roleId   The ID of the Role to assign to the user.
   * @return bool   True if resource was created, false otherwise.
   */
  protected function createUser($input, $roleId)
  {
    $now = Carbon::now()->toDateTimeString();

    $user = new User;
    $user->username = $input['username'];
    $user->email = $input['email'];
    $user->password = Hash::make($input['password']);
    $user->status = $input['status'];
    $user->role_id = $roleId;
    $user->created_at = $now;
    $user->updated_at = $now;

    if ($user->save()) return $user;
    return null;
  }

}
