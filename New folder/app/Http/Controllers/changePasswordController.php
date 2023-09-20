<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\changePasswordRequest;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\User;

class changePasswordController extends Controller
{
    public function process(changePasswordRequest $request){
        return $this->getPasswordResetTableRow($request)->count() > 0 ?
                $this->changePassword($request): $this->tokenNotFoundResponse();

    }

    public function getPasswordResetTableRow($request){
        return DB::table('password_resets')->where(['email'=>$request->email, 'token'=>$request->resetToken]);
    }

    public function changePassword($request){
        $user = User::whereEmail($request->email)->first();
        $user->update(['password'=>$request->password]);
        $this->getPasswordResetTableRow($request)->delete();
        return response()->json(['data'=>'password  Successfully Changed'],Response::HTTP_CREATED);
    }
    public function tokenNotFoundResponse(){
        return response()->json(['error'=> 'Token or Email is Incorrect'],Response::HTTP_UNPROCESSABLE_ENTITY);
    }


}
