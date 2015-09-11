<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\Api\V1\Controller\UserController;
use Mypleasure\Http\Requests\SendInviteRequest;
use Mypleasure\Invite;
use Carbon\Carbon;

class InviteController extends BaseController {

  public function __construct()
  {
    $this->middleware('api.auth', ['only' => ['send']]);
    $this->middleware('guest', ['only' => ['claim']]);
  }

  public function send(SendInviteRequest $request)
  {
    $user = \JWTAuth::parseToken()->toUser();

    $defaultMsg = "Hey\n\nYou've been invited to try myPleasure (beta),";
    $defaultMsg .= "a web app to make binge watching much easier.\n";
    $defaultMsg .= "You can use it to gather in one place all videos, from all sources you like ";
    $defaultMsg .= "on the Internet. Yes... even adult videos.\n\n";
    $defaultMsg .= "Click or copy the link below to join:\n";

    $invite = new Invite;
    $invite->email = $request->input('email');
    $invite->from_id = $user->id;
    $invite->message = $request->input('message', $defaultMsg);
    $invite->code = md5($invite->email . '_from_' . $user->id);
    $invite->created_at = Carbon::now();
    $invite->save();

    $url = "/{$invite->email}/{$invite->code}";
    $subject = 'myPleasure beta invite';

    \Mail::send('emails.invite', ['body' => $invite->message, 'url' => $url], function ($m) use (&$invite, &$subject) {
      $m->to($invite->email)->subject($subject);
    });

    return response()->json(['status_code' => 200, 'message' => 'Invite sent successfully.'], 200);
  }

  public function claim(UserController $userController, $email, $code)
  {
    $validator = \Validator::make([
      'email' => $email,
      'code'  => $code
      ], [
      'email' => 'required|email',
      'code'  => 'required|min:5'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status_code' => 422,
        'message' => 'Validation of parameters failed.',
        'errors' => $validator->errors()
      ], 422);
    }

    $invite = Invite::where('code', $code)
                    ->where('email', $email)
                    ->first();

    if (!$invite) {
      return response()->json([
        'status_code' => 404,
        'message' => 'Invite not found.',
      ], 404);
    }

    $invite->claimed_at = Carbon::now();
    $invite->save();

    return $userController->onboardFromInvite($invite);
  }

}