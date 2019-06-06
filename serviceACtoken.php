<!-- this token script made using this documentation 
https://developers.google.com/identity/protocols/OAuth2ServiceAccount
-->


<?php 
function getToken(){

// upload your credentials file in same folder & rename it to  : credentials.json
$credentials = file_get_contents('./credentials.json');

/* if store your your credentials file somewhere else ?  replace by url & file name as below

$credentials = file_get_contents('file_address/file_name.json');

*/

//Decode JSON
$credentials = json_decode($credentials,true);
// set variables 
$key = $credentials[private_key];
$client_email = $credentials[client_email];

// base64 incoding function
function base64url_encode($data) { 
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
}

//Google's Documentation of Creating a JWT: https://developers.google.com/identity/protocols/OAuth2ServiceAccount#authorizingrequests

//{Base64url encoded JSON header}
$jwtHeader = base64url_encode("{
    'alg' : 'RS256',
    'typ' : 'JWT'
}");
//{Base64url encoded JSON claim set}
$now = time();
$exp =  $now + 3600; // token is valid for 1 hour (3600 seconds )
$jwtClaim = base64url_encode("{
    'iss' : $client_email,
    'scope' : 'https://www.googleapis.com/auth/dialogflow',
    'aud' : 'https://www.googleapis.com/oauth2/v4/token',
    'exp' : $exp,
    'iat' : $now
}");


$grant_type = "urn:ietf:params:oauth:grant-type:jwt-bearer";
$grant_type = urlencode($grant_type);

        //input for sign: {Base64url encoded header}.{Base64url encoded claim set}
        $sig_input=$jwtHeader.".".$jwtClaim;

        //create signature      
        openssl_sign(
                $sig_input,
                $sig,
                $key,
                "sha256WithRSAEncryption"
        );

        $jwtSign=base64url_encode($sig);

        //JWT = {Base64url encoded header}.{Base64url encoded claim set}.{Base64url encoded signature}
        $jwtAssertion = $jwtHeader.".".$jwtClaim.".".$jwtSign;
		
		$data_string = "grant_type=".$grant_type."&assertion=".$jwtAssertion;
		$url = "https://www.googleapis.com/oauth2/v4/token";
		$curl        = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/x-www-form-urlencoded'
        ));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($curl);
        curl_close($curl);
		$output = json_decode($output, true);
		return $output;
		}
		
		
		// finally call the function 
		
		echo getToken();
		
		
?>