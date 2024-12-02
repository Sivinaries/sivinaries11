<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{

    public function loginQr(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $qrToken = Str::random(40);
        $user->qr_token = $qrToken; // Save the token to the user's record
        $user->save();
    
        $qrUrl = "https://sivinaries.my.id/signin?qrToken=" . $qrToken . "&name=" . urlencode($user->name);
    
        $qrCode = QrCode::size(400)->generate($qrUrl);
    
        $filename = "qrcodes/" . Str::random(10) . ".svg";
        Storage::disk('public')->put($filename, $qrCode);
    
        return view('qrcode', ['filename' => $filename, 'user' => $user]);
    }

    








    


    



    // public function loginQr(Request $request, $id)
    // {
    //     $currentIp = $request->getClientIp();
    //     Log::info('Client IP Address:', ['ip' => $currentIp]);

    //     $user = User::findOrFail($id);

    //     if ($request->isMethod('post')) {
    //         $qrToken = $request->input('qrToken');  // Get the QR token from the request
    //         $user = User::where('qr_token', $qrToken)->first();

    //         if (!$user) {
    //             return redirect()->route('error.page')->with('message', 'Invalid QR token');
    //         }

    //         $deviceId = $request->input('deviceId');
    //         // Log deviceId to ensure it's being passed
    //         Log::info('Received Device ID for QR Login:', ['deviceId' => $deviceId]);

    //         // Generate the email based on deviceId
    //         $generatedEmail = $deviceId . '@device.com';  // Use deviceId as part of the email

    //         // Check if the user exists based on the generated email
    //         $existingUser = User::where('email', $generatedEmail)->first();

    //         if (!$existingUser) {
    //             // If user does not exist, create a new user and associate the generated email
    //             $user->email = $generatedEmail;
    //             $user->save();
    //         } else {
    //             // Use the existing user
    //             $user = $existingUser;
    //         }

    //         // Update the user's device ID and IP address
    //         $user->device_id = $deviceId;
    //         $user->ip_address = $currentIp;
    //         $user->save();

    //         // Log the user in
    //         Auth::login($user);

    //         return redirect()->route('user-home');
    //     }

    //     // Generate QR code URL with deviceId
    //     $qrToken = $user->qr_token;
    //     $qrUrl = "http://192.168.100.22:8000/signin?qrToken=" . $qrToken . "&deviceId=" . $user->device_id; // Include deviceId

    //     // Generate QR code image
    //     $qrCode = QrCode::size(400)->generate($qrUrl);

    //     $filename = "qrcodes/" . Str::random(10) . ".svg";
    //     Storage::disk('public')->put($filename, $qrCode);

    //     return view('qrcode', ['filename' => $filename, 'user' => $user]);
    // }
    // public function LoginQr($id)
    // {
    //     $user = User::findOrFail($id);
    //     $qrToken = $user->qr_token;
    //     $qrUrl = "http://192.168.1.10:8000/signin?qrToken=" . $qrToken; // Directly hardcoding the URL
    //     $qrCode = QrCode::size(400)->generate($qrUrl);

    //     $filename = "qrcodes/" . Str::random(10) . ".svg";
    //     Storage::disk('public')->put($filename, $qrCode);

    //     return view('qrcode', ['filename' => $filename, 'user' => $user]);
    // }


    // public function UserQr($id)
    // {
    //     $user = User::findOrFail($id); 
    //     $qrToken = $user->qr_token;

    //     $url = 'https://beilcoff.shop/login?qrToken=' . $qrToken;
    //     $qrCode = QrCode::size(400)->generate($url);

    //     $filename = "qrcodes/" . Str::random(10) . ".svg"; // Generating a random filename
    //     Storage::disk('public')->put($filename, $qrCode);

    //     return view('qrcode', ['filename' => $filename, 'user' => $user]);
    // }

}
