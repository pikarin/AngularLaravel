<?php

namespace App\Http\Controllers;

use App\User;
use JWTAuth;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticateController extends Controller
{

    public function __construct()
    {
        // Apply the jwt.auth middleware to all methods in this controller
        // except for the authenticate method. We don;t want to prevent
        // the user from retrieving their token if the don't already have it
        $this->middleware('jwt.auth', ['except' => ['authenticate']]);
    }

    public function index()
    {
        // Retrieve all the users in the database and return them
        $users = User::all();
        return $users;
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // verify the credentials and create a token for user
            if(! $token = JWTAuth::attempt($credentials))
            {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            //  something went wrong
            return response()->json(['error' => 'could_not_create_token']);
        }

        //if no errors are encountered we can return a JWT
        return response()->json(compact('token'));
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }
}
