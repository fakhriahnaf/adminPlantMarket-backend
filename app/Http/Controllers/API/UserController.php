<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidatonRules;
use App\Helpers\ResponseFormatter;
//use Dotenv\Validator;
use Exception;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use PasswordValidationRules;
    //
    public function login (Request $request)
    {
        
        //validasi input
        try{
            $request -> validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);
            //mengecek credentials(login)
            $credentials = request(['email','password']);
            if(!Auth::attempt($credentials)) {
                return ResponseFormatter::error(['message' => 'Unautorized'], 'Autentification Failed', 500);
            }

            //jika berhasil maka tidak sesuai maka di beri error
            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }
            //jika berhasil login
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer', 
                'User' => $user], 'Authenticated');

            
        } catch (Exception $error){
            return ResponseFormatter::error([
                'message' => 'Something when wrong',
                'error' => $error
            ], 'Authentification Failed', 500);
        }
    }

    public function Register(Request $request)
    {
        try{
            $request->validate([
                'name' => ['required','string', 'max:255'],
                'email'=> ['required','string', 'email','max:255','unique:users'],
                'password' => $this->passwordRules()
            ]);

            User::create([
                'name' => $request->name,
                'email'=> $request->email,
                'address'=> $request->address,
                'phoneNumber' => $request->phoneNumber,
                'portalCode' =>$request->portalCode,
                'city' =>$request->city,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first;
            $tokenResult = $user->createToken('Auth Token')->plainToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        } catch (Exception $error){
            return ResponseFormatter::error([
                'message' => 'something went wrong',
                'error' => $error
            ], 'Authentification Failed', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'TokenRevoked');
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(), 'Data profile user berhasil diambil'
        );
    }

    public function updateProfile(Request $request)
    {
        $data =$request->all();
        $user = Auth::user();
        $user ->update($data);

        return ResponseFormatter::successs($user, 'Profile updated');
    }

    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'file' => 'required|image|max:2048'
        ]);
        if($validator->fails())
        {
            return ResponseFormatter::error([
                'error'=>$validator->errors()
            ], 'update photo failed', 401 );
        }
        if($request->file('file'))
        {
            $file= $request->file->store('assets/user', 'public');

            //simpan foto ke database user
            $user = Auth::user();
            $user->profile_photo_path = $file;
            $user->update();

            return ResponseFormatter::success([$file], 'file successfull upload');
        }
    }
}
