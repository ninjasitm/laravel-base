<?php

namespace Nitm\Api\Http\Controllers\Auth;

use Nitm\Api\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Nitm\Content\Services\FacebookService;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    use FacebookService;

    protected $redirectTo = '/home';
    protected $facebookService;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToProviderFacebook()
    {
        return $this->redirect();
    }

    public function handleProviderCallbackFacebook()
    {
        return $this->callback();
    }

    protected function credentials(Request $request)
    {
        $credentials = [
            $this->username() => strtolower($request->get($this->username())),
            "password" => $request->get("password")
        ];

        return $credentials;
    }

    /**
    * @SWG\Post(
    *   path="/oauth/token",
    *   tags={"Authentication"},
    *   description="Login endpoints",
    *   summary="User Authentication",
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="Retrieve Token",
    *     required=true,
    *     type="string",
    *         @SWG\Schema(
    *             type="object",
    *             properties={
    *                 @SWG\Property(property="grant_type",    type="string", example="password"),
    *                 @SWG\Property(property="client_id",     type="integer", example="2"),
    *                 @SWG\Property(property="client_secret", type="string", example="Cn9MwmsEr5DTjUgkLGgBdZjpIg87q3ZxWz5pd7Ay"),
    *                 @SWG\Property(property="username",      type="string", example="secret@secret.com"),
    *                 @SWG\Property(property="password",      type="string", example="secret")
    *             }
    *         )
    *   ),

    *   @SWG\Response(
    *       response=200, description="successful operation",
    *       @SWG\Schema(
    *             type="object",
    *             properties={
    *                 @SWG\Property(property="token_type",    type="string", example="Bearer"),
    *                 @SWG\Property(property="expires_in",    type="integer", example="1296000"),
    *                 @SWG\Property(property="access_token",  type="string", example="Cn9MwmsEr5DTjUgkLGgBdZjpIg87q3ZxWz5pd7Ay"),
    *                 @SWG\Property(property="refresh_token", type="string", example="def502002c9349b26eeeab5685509b26e3fea2f1b0"),
    *             }
    *       )
    *   ),
    *   @SWG\Response(response=401,             description="Invalid credentials",
    *       @SWG\Schema(
    *             type="object",
    *             properties={
    *                 @SWG\Property(property="error",         type="string", example="invalid_credentials"),
    *                 @SWG\Property(property="message",       type="string", example="The user credentials were incorrect.")
    *             }
    *       )
    *   ),
    *   @SWG\Response(response=500,             description="internal server error")
    * )
    */




    /**
    * @SWG\Post(
    *   path="/oauth/token ",
    *   tags={"Authentication"},
    *   description="Login endpoints",
    *   summary="User Refresh Token",
    *   @SWG\Parameter(
    *     name="body",
    *     in="body",
    *     description="Retrieve New Token",
    *     required=true,
    *     type="string",
    *         @SWG\Schema(
    *             type="object",
    *             properties={
    *                 @SWG\Property(property="grant_type",    type="string", example="refresh_token"),
    *                 @SWG\Property(property="refresh_token", type="string", example="def502002c9349b26eeeab5685509b26e3fea2f1b0"),
    *                 @SWG\Property(property="client_id",     type="integer", example="2"),
    *                 @SWG\Property(property="client_secret", type="string", example="Cn9MwmsEr5DTjUgkLGgBdZjpIg87q3ZxWz5pd7Ay")
    *             }
    *         )
    *   ),

    *   @SWG\Response(
    *       response=200, description="successful operation",
    *       @SWG\Schema(
    *             type="object",
    *             properties={
    *                 @SWG\Property(property="token_type",    type="string", example="Bearer"),
    *                 @SWG\Property(property="expires_in",    type="integer", example="1296000"),
    *                 @SWG\Property(property="access_token",  type="string", example="Cn9MwmsEr5DTjUgkLGgBdZjpIg87q3ZxWz5pd7Ay"),
    *                 @SWG\Property(property="refresh_token", type="string", example="def502002c9349b26eeeab5685509b26e3fea2f1b0"),
    *             }
    *       )
    *   ),
    *   @SWG\Response(response=401,             description="Invalid Client",
    *       @SWG\Schema(
    *             type="object",
    *             properties={
    *                 @SWG\Property(property="error",         type="string", example="invalid_client"),
    *                 @SWG\Property(property="message",       type="string", example="Client authentication failed")
    *             }
    *       )
    *   ),
    *   @SWG\Response(response=500,             description="internal server error")
    * )
    */
}