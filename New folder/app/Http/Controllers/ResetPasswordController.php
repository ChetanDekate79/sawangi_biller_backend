<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Symfony\Component\HttpFoundation\Response ;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    //


    public function sendEmail(Request $request){
        if(!$this->validateEmail($request->email)){
            return $this->failedResponse();
        }
        $this->send($request->email);
        return $this->successResponse();
    }
    public function send($email){
        $token = $this->createToken($email);
        Mail::to($email)->send(new ResetPasswordMail($token));
    }

    public function createToken($email){
        $oldtoken = DB::table('password_resets')->where('email',$email)->first();
        if($oldtoken){
            return $oldtoken->token;
        }
        $token = str_random(60);
        $this->saveToken($email,$token);
       return $token;

    }

    public function saveToken($email,$token){
        DB::table('password_resets')->insert([
            'email'=>$email,
            'token' =>$token,
            'created_at'=>Carbon::now()
        ]);

    }
    public function validateEmail($email){

        return !!User::Where('email',$email)->first();
    }

    public function successResponse(){

        return response() -> json([
            'data' => 'Psword Reset Link Sent Successfully'
        ], Response::HTTP_OK);
    }

    public function failedResponse(){

        return response() -> json([
            'error' => 'Email doesn\'t found in our database'
        ], Response::HTTP_NOT_FOUND);
    }
}
