<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use lbuchs\WebAuthn\WebAuthn;

class WebAuthTestController extends Controller
{
    private $webAuth;

    public function __construct()
    {
        $this->webAuth = new WebAuthn("Test", "test");
    }

    public function getRegistrationOptions()
    {
        $args = $this->webAuth->getCreateArgs(
            1,
            'stone',
            'stone',
            60,           // timeout in seconds
            false,        // requireResidentKey
            'discouraged' // userVerification
        );

        $challenge = $this->webAuth->getChallenge()->getBinaryString();

        return response()->json([
            'args' => $args
        ]);
    }

    public function register(Request $request)
    {
        // // Extract and decode the MIME-encoded challenge
        // $mimeEncodedChallenge = $request["challenge"]; // "=?BINARY?B?91hYlkyhgDI6JHBzRNOZhl0UHlnHnhYz+mhhAQ1+Vuo=?="

        // // 1. Remove the MIME header/footer (=?BINARY?B? ... ?=)
        // $base64Challenge = preg_replace('/^=\?BINARY\?B\?(.+)\?=$/', '$1', $mimeEncodedChallenge);

        // // 2. Base64-decode the inner content
        // $binaryChallenge = base64_decode($base64Challenge);
        // dd($binaryChallenge);
        // dd($challenge);
        $reg = $this->webAuth->processCreate(
            base64_decode($request['credential']["response"]["clientDataJSON"]),
            base64_decode($request['credential']["response"]["attestationObject"]),
            // $binaryChallenge,
            base64_decode($request["challenge"]),
            false// requireUserVerification
        );

        return response()->json([
            'credentialId' => base64_encode($reg->credentialId),
            'publicKey'    => base64_encode($reg->credentialPublicKey),
            'signCount'    => $reg->signatureCounter,
        ]);
    }

    public function getLoginOptions()
    {
        $args = $this->webAuth->getGetArgs([
            [
                // 'id' => $storedCredentialId,
                'type'       => 'public-key',
                'transports' => ['usb', 'ble', 'nfc', 'internal'],
            ],
        ], 300, 'discouraged');
        // timeout, userVerification
        $challenge = $this->webAuth->getChallenge()->getBinaryString();

        return response()->json([
            'args' => $args,
        ]);
    }

    public function login()
    {
        $reg = $this->webAuth->processGet(
            base64_decode($data->credentialId),
            base64_decode($data->clientDataJSON),
            base64_decode($data->authenticatorData),
            base64_decode($data->signature),
            $_SESSION['challenge']
        );

        // Update signature counter in DB
        $stored['signCount'] = $credential->signCount;

        return response()->json([
            'credentialId' => base64_encode($reg->credentialId),
            'publicKey'    => base64_encode($reg->credentialPublicKey),
            'signCount'    => $reg->signatureCounter,
        ]);
    }
}
