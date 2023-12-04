<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\user;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function login(Request $req){
    $credentials = $req->only('email', 'password');
    try {
        if(! $token = JWTAuth::attempt($credentials))
    {
        return response()->json(['status'=>'error','message' => 'Email / Password Salah']);
    }
    } catch (JWTException $e) {
        return response()->json(['status'=>'error','message' => 'could_not_create_token', 500]);
    }
        return response()->json(['status'=>'success','message' => 'Sukses Login', 'token'=>$token]);
    }

    public function register(Request $req) {    

        $validator = Validator::make($req->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson());
        }
        $user = User::create([
            'name' => $req->get('name'),
            'email' => $req->get('email'),
            'password' => Hash::make($req->get('password')),
        ]);
            $token = JWTAuth::fromUser($user);
            return response()->json(compact('user','token'),201);
    }

    public function getAuthenticatedUser(){
        try{
            if(! $user = JWTAuth::parseToken()->authenticate()){
                return \Response::json(['user_not_found'], 404);
            }
        }
        catch(Tymon\JWTAuth\Exceptions\TokenExpiredException $e){
            return \Response::json(['token_expired'], $e->getStatusCode());
        }
        catch(Tymon\JWTAuth\Exceptions\TokenInvalidException $e){
            return \Response::json(['token_invalid'], $e->getStatusCode());
        }
        catch(Tymon\JWTAuth\Exceptions\JWTException $e){
            return \Response::json(['token_absent'], $e->getStatusCode());
        }
        return \Response::json(['status'=>'success','message' =>$user]);
        /*return \Response::json(array('user' => 'Steve', 'state' => 'CA'));*/
    }
}
