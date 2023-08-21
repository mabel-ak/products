<?php

namespace App\Http\Controllers;
use App\Events\SignUpEvent;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Verificationcode;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
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
            'phone_number' => 'required|string|max:11|unique:users'
        ]);
        if($validator->fails()){
        return response () ->json ( $validator->errors() ->toJson(),400);
        }
        $user = User::create(array_merge(
        $validator->validated(),
        ['password'=>bcrypt ($request->password) ]
        ));
        new SignUpEvent($user);
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
        }

        $token = $token = auth()->attempt(['email' => $request->email, 'password' => $request->password]);
        if (!$token){
            return response()->json(['error'=>'Unauthorized'],401);
        }
        

            return response()->json([
                'message'=> 'You have successfully logged in',
                'token' => $token
            ],201);
       
    }
    
    // Generate OTP
    public function generate(Request $request)
    {
        # Validate Data
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number'
        ]);

        //Generate An OTP
        $verificationcode = $this->generateOtp($request->phone_number);

        //Return with OTP
        return redirect()->route('otp.verification')->with(['message'=> 'success']);
    }
    public function generateOtp($phone_number)
    {
        $user = User::where('phone_number', $phone_number)->first();

        # User Does not Have Any Existing OTP
        $verificationcode = Verificationcode::where('user_id', $user->id)->latest()->first();

        $now = Carbon::now();

        if($verificationcode && $now->isBefore($verificationcode->expire_at)){
            return $verificationcode;
        }

        // Create a New OTP
        return VerificationCode::create([
            'user_id' => $user->id,
            'otp' => rand(123456, 999999),
            'expire_at' => Carbon::now()->addMinutes(10)
        ]);
    }

    public function verification($user_id)
    {
        return view('auth.otp-verification')->with([
            'user_id' => $user_id
        ]);
    }

    public function loginWithOtp(Request $request)
    {
        #Validation
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required'
        ]);

        #Validation Logic
        $verificationCode = Verificationcode::where('user_id', $request->user_id)->where('otp', $request->otp)->first();

        $now = Carbon::now();
        if (!$verificationCode) {
            return redirect()->back()->with('error', 'Your OTP is not correct');
        }elseif($verificationCode && $now->isAfter($verificationCode->expire_at)){
            return redirect()->route('otp/login')->with('error', 'Your OTP has been expired');
        }

        $user = User::whereId($request->user_id)->first();

        if($user){
            // Expire The OTP
            $verificationCode->update([
                'expire_at' => Carbon::now()
            ]);

            Auth::login($user);

        } 

        return redirect()->route('otp/login')->with(['message'=> 'Your Otp is not correct']);
    }



    public function logout()
    {
       auth()->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
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
    