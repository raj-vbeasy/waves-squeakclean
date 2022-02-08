<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Traits\GoogleClient;
use App\User;
use Exception;
use Google_Service_Drive;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    use RegistersUsers, GoogleClient;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param array $data
	 * @return User
	 * @throws Exception
	 */
    protected function create(array $data)
    {
    	return User::create(
		    [
                'name' => $data['name'],
			    'email' => $data['email'],
			    'password' => Hash::make($data['password']),
			    'folder_id' => $this->createFileOrFolder(
			    	$data['name'],
                    'application/vnd.google-apps.folder',
                    '',
                    $data['email']
				) ?? ''
		    ]
	    );
    }
}
