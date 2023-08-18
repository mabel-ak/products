<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use Validator;
class AuthController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api',['except'=>['login','register']]);
    }
    //Methods for authentication functionality
    public function register (Request $request){
        $validator =Validator::make($request->all(),[
            'first_name'=> 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6', 
            'confirm_password' => 'required|string|min:6',
            'phone_number' => 'required|string|max:20|unique:users'
        ]);
        if($validator->fails()){
        return response () ->json ( $validator->errors() ->toJson(),400);
        }
        $user = User::create(array_merge(
        $validator->validated(),
        ['password'=>bcrypt ($request->password) ]
        ));
        
        return response () -> json([
        'message'=> 'User successfully registered',
        'user'=>$user
        ],201);
        }

        
        public function login (Request $request){
        $validator =Validator::make($request->all(),[
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6', 
        ]);
        if($validator->fails()){
        return response () ->json ( $validator->errors() ->toJson(),422);
        if (!$token=auth()->attempt ($validator->validated())){
            return response()->json(['error'=>'Unauthorized'],401);
        }}

        return response ()->json([
            'message'=> 'You have successfully logged in',
        ],201);
    }
    public function logout() {
        auth()->logout();
        return response()->json([
            'message' => 'User logged out'
        ]);
    }

        
        public function createNewToken($token)
        {
            return response()->json([
                'access_token'=>$token,
                'token_type'=>'bearer',
                'expires_in'=>auth()->factory()->getTTL()*60,
                'user'=>auth()->user()

            ]);
        
        }
    }