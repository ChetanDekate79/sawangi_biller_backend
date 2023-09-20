<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignUpRequest;
use App\User;
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:api'],['except' => ['login','signup','logout']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

       // return response()->json($this->guard()->attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1]));

        if ($token = $this->guard()->attempt($credentials )) {
            if($this->checkStatus($request)){
                $this->inncrementLoginCount($request);
                return $this->respondWithToken($token);
            }
            else{
                return response()->json(['error' => 'User Inactive, Please Contact to admin for Activation'], 401);
            }

        }

        return response()->json(['error' => 'Email or Password deos\'t Exist'], 401);
    }

    public function signup(Request $request)
    {
        User::create($request->all());
       // return $this->login($request);

return response()->json(['message' => 'Registration  successfully completed , Please cantact the  admin for your account  activation']);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }

    public function checkStatus($request){
        if(auth()->user()->status){
            return true;
        }
        $this->logout($request);
        return false;
    }

    public function inncrementLoginCount($request){

      $user = User::whereEmail($request->email)->first();
      $user->update(['login_count'=>auth()->user()->login_count+1]);
    }
}
