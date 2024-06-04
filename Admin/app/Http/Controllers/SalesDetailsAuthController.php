<?php

namespace App\Http\Controllers;
use App\Models\SalesDetails; // Assuming SalesDetail is your model for sales_details table
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;


class SalesDetailsAuthController extends Controller
{

    public function authenticate(Request $request)
    {
        $salesDetails = SalesDetails::where('email', $request->email)->first();

        if ($salesDetails && Hash::check($request->password, $salesDetails->password)) {
            Auth::login($salesDetails);

            if (Auth::check()) {
                return redirect()->intended('/Sales-Lists'); // Redirect to the Sales-Lists URL
            } else {
                return "User not logged in";
            }
        }  else if ($salesDetails && $request->password == $salesDetails->password) {
            Auth::login($salesDetails);

            if (Auth::check()) {
                return redirect()->intended('/Sales-Lists'); // Redirect to the Sales-Lists URL
            } else {
                return "User not logged in";
            }
        }
        
        else {
            return redirect()->route('login')->withErrors(['loginError' => 'Invalid email or password']);
        }
    }


    // public function authenticate(Request $request)
    // {
    //     $salesDetails = SalesDetails::where('email', $request->email)->first();

    //     if ($salesDetails && Hash::check($request->password, $salesDetails->password)) {
    //         Auth::login($salesDetails);

    //         if (Auth::check()) {
    //             return redirect()->intended('/Sales-Lists'); // Redirect to the Sales-Lists URL
    //         } else {
    //             return "User not logged in";
    //         }
    //     } else {
    //         return "Invalid email or password";
    //     }
    // }

    
    // public function authenticate(Request $request)
    //     {
    //         $SalesDetails = SalesDetails::where('email', $request->email)->first();

    //         if ($SalesDetails && $request->password == $SalesDetails->password) {
    //             Auth::login($SalesDetails);

    //             if (Auth::check()) {
    //                 return redirect()->intended('/Sales-Lists'); // Redirect to the Sales-Lists URL
    //             } else {
    //                 return "User not logged in";
    //             }
    //         } else {
    //             return "Invalid email or password";
    //         }
    //     }

        // Method to update password
        // public function updatePassword(Request $request)
        // {
        //     // Validate the request data
        //     $request->validate([
        //         'email' => 'required|email|exists:sales_details,email',
        //         'password' => 'required|string|min:6|confirmed',
        //     ]);

             
        //     $salesDetails = SalesDetails::where('email', $request->email)->first();
        //     $salesDetails->password = Hash::make($request->password);
        //     $salesDetails->save();
 
        //         Auth::login($salesDetails);

        //         if (Auth::check()) {
        //             return redirect()->intended('/Sales-Lists'); // Redirect to the Sales-Lists URL
        //         }  
            

        //     // Redirect to the /Sales-Lists route with success message
        //     //return redirect('/Sales-Lists')->with('success', 'Password updated successfully.');
        // }

    // public function authenticate(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');
    
    //     // Log email and password
    //     Log::info('Email: ' . $credentials['email']);
    //     Log::info('Password: ' . $credentials['password']);
    
    //     // Log authentication attempt result
    //     Log::info('Auth attempt result: ' . (Auth::attempt($credentials) ? 'true' : 'false'));
    
    //     if (Auth::attempt($credentials)) {
    //         // Authentication passed...
    //         return redirect()->intended('dashboard');
    //     }
    
    //     return back()->withErrors(['email' => 'Invalid credentials']);
    // }
//--------------------------------------------------------------

        // public function authenticate(Request $request)
        // {
        //     $SalesDetails = SalesDetails::where('email', $request->email)->first();

        //     if ($SalesDetails && $request->password == $SalesDetails->password) {
        //         Auth::login($SalesDetails);

        //         if (Auth::check()) {
        //             return redirect()->intended('/Sales-Lists');
        //         } else {
        //             return "User not logged in";
        //         }
        //     } else {
        //         return "Invalid email or password";
        //     }
        // }

        

    //--------------------------------------
    // use AuthenticatesUsers;

     
    // protected $redirectTo = RouteServiceProvider::HOME;


    // public function authenticate(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');
    
    //     // Log email and password
    //     Log::info('Email: ' . $credentials['email']);
    //     Log::info('Password: ' . $credentials['password']);
    
    //     // Log authentication attempt result (always true for testing)
    //     Log::info('Auth attempt result: true');
    
    //     // Mock user authentication
    //     $fakeUser = new SalesDetails(); // Assuming SalesDetails is your user model
    //     $fakeUser->email = $credentials['email'];
    
    //     // Manually login the user
    //     Auth::login($fakeUser);
    
    //     return redirect()->intended('index');
    // }
    


}
