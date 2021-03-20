<?php

namespace App\Http\Controllers\AuthAdmin;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\Admin;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use SebastianBergmann\Environment\Console;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/admin';


    public function __construct()
    {
        $this->middleware('guest:admin');
    }

    public function showRegistrationForm()
    {
        return view('authAdmin.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->create($data);

        //LOGIN AFTER NEW USER REGISTERED SUCCESSFULY
        if($user) {

            error_log(sprintf($this->colorFormat['green'], 'INFO: NEW ADMIN SUCCESFULLY CREATED'));
            event(new Registered($user));

            try {

                error_log(sprintf($this->colorFormat['yellow'], 'LOGIN ATTEMPT FROM ADMIN: ' . $request->input('email')));
                Auth::guard('admin')->loginUsingId($user->id);

                return redirect()->route('admin.dashboard');

            } catch (\Throwable $th) {
                return $th;
            }

        }

        if(!$user) {
            return "Failed create new account";
        }
        
    }

    protected function create(array $data)
    {
        return Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        
    }
}
