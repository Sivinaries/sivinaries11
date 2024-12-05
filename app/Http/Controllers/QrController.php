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
