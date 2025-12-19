<?php

namespace App\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use App\Models\User;
use App\Models\WebAuthnCredential;
use Illuminate\Support\Facades\Auth;

class WebAuthnController extends Controller
{
    private $webAuthn;
    
    public function __construct()
    {
        $this->webAuthn = new WebAuthn(
            'Your App Name', // App name
            'localhost', // App ID (domain)
            'none' // attestation conveyance
        );
    }
    
    // Get registration options
    public function registerOptions(Request $request)
    {
        // $user = $request->user();
        $user = User::where('id', 1)->first();
        
        $createArgs = $this->webAuthn->getCreateArgs(
            $user->id,
            $user->email,
            $user->name,
            20, // timeout in seconds
            requireResidentKey: false
        );
        Cache::put('webauthn_register_challenge_1', $this->webAuthn->getChallenge());
        
        session(['webauthn_register_challenge' => $this->webAuthn->getChallenge()]);
        
        return response()->json($createArgs);
    }
    
    // Process registration
    public function register(Request $request)
    {
        $user = User::where('id', 1)->first();
        $challenge = session('webauthn_register_challenge');
        $challenge = Cache::get('webauthn_register_challenge_1');
        
        $data = $request->all();

        $clientDataJSON = base64_decode($data['clientDataJSON']);
        $attestationObject = base64_decode($data['attestationObject']);
        
        try {
            $credential = $this->webAuthn->processCreate(
                $clientDataJSON,
                $attestationObject,
                $challenge
            );

            WebAuthnCredential::create([
                'user_id' => $user->id,
                'credential_id' => base64_encode($credential->credentialId),
                'public_key' => base64_encode($credential->credentialPublicKey),
                'counter' => $credential->signatureCounter == null ? 0 : $credential->signatureCounter,
            ]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    // Get login options
    public function loginOptions(Request $request)
    {
        $credentials = WebAuthnCredential::where('user_id', 1)->get();

        $credentials = json_decode($credentials, true) ?? [];

        $credentialIds = array_map(function ($cred) {
            return base64_decode($cred['credential_id']);
        }, $credentials);

        $getArgs = $this->webAuthn->getGetArgs($credentialIds, 20); // 20 seconds timeout
        
        Cache::put('webauthn_login_challenge_1', $this->webAuthn->getChallenge(), now()->addMinutes(5));
        session(['webauthn_login_challenge' => $this->webAuthn->getChallenge()]);

        return response()->json($getArgs);
    }
    
    public function login(Request $request)
    {
        $userId = $request->input('user_id');
        $challenge = session()->get('webauthn_login_challenge');
        $challenge = Cache::get('webauthn_login_challenge_1');

        
        $data = $request->all();
        $clientDataJSON = base64_decode($data['clientDataJSON']);
        $authenticatorData = base64_decode($data['authenticatorData']);
        $signature = base64_decode($data['signature']);
        $credentialId = base64_decode($data['id']);
        $userHandle = $userId;
        
        $credential = WebAuthnCredential::where('credential_id', base64_encode($credentialId))->first();

        if (!$credential) {
            return response()->json(['error' => 'Credential not found'], 400);
        }
        
        try {
            $this->webAuthn->processGet(
                $clientDataJSON,
                $authenticatorData,
                $signature,
                base64_decode($credential->public_key),
                $challenge,
                $userHandle,
                $credential->counter
            );
            
            // Update counter
            $credential->update(['counter' => $credential->counter + 1]);
            
            
            // Auth::loginUsingId($credential->user_id);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}