<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthController extends ApiController
{
    public function register(Request $request)
    {

        $validator=Validator::make($request->all(),[
            'name'=>'required|string',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string',
            'c_password'=>'required|same:password',
            'address'=>'required|string',
            'cell_phone'=> 'required',
            'postal_code'=>'required',
            'province_id'=>'required',
            'city_id'=>'required',
        ]);
        if ($validator->fails()) {
           return $this->errorResponse($validator->messages(),422);
        }
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'address'=>$request->address,
            'cell_phone'=>$request->cell_phone,
            'postal_code'=>$request->postal_code,
            'province_id'=>$request->province_id,
            'city_id'=>$request->city_id,


        ]);
        $token=$user->createToken('myApp')->plainTextToken;
            return $this->successResponse([
                'user'=>$user,
                'token'=>$token,
            ],201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',

        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->errorResponse('user not found', 401);
        }
        if (!Hash::check($request->password, $user->passwoed)) {
            return $this->errorResponse('user not found', 401);
        }
        $token=$user->createToken('myApp')->plainTextToken;
        return $this->successResponse([
            'user'=>$user,
            'token'=>$token,
        ],200);
    }
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json('logged out',200);
    }


}
