<?php

namespace App\Routes\Http\Web\Controllers;

use App\Modules\Discourse\Services\Authentication\DiscourseAuthService;
use App\Modules\Forums\Exceptions\BadSSOPayloadException;
use App\Modules\Accounts\Services\AccountLinkService;
use App\Routes\Http\Web\WebController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\Factory as Validation;
use Illuminate\Contracts\Auth\Guard as Auth;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class LoginController extends WebController {

    /**
     * @var DiscourseAuthService
     */
    private $discourseAuthService;

    /**
     * @var AccountLinKService
     */
    private $accountLinkService;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var Client
     */
    private $client;


    public function __construct(
        DiscourseAuthService $discourseAuthService,
        AccountLinkService $accountLinkService,
        Auth $auth,
        Client $client
    ) {
        $this->discourseAuthService = $discourseAuthService;
        $this->accountLinkService = $accountLinkService;
        $this->auth = $auth;
        $this->client = $client;
    }

    public function showLoginView(Request $request) {
        // login route should have a valid payload in the url
        // generated by discourse when being redirected here
        $sso        = $request->get('sso');
        $signature  = $request->get('sig');

        if($sso === null || $signature === null) {
            return redirect()->route('front.home');
        }

        // validate that the given signature matches the
        // payload when signed with our private key. This
        // prevents any payload tampering
        if($this->discourseAuthService->isValidPayload($sso, $signature) === false) {
            // TODO: forged payload - handle it here
            abort(400);
        }

        // ensure that the payload has all the necessary
        // data required to create a new payload after
        // authentication
        $payload = null;
        try {
            $payload = $this->discourseAuthService->unpackPayload($sso);
        } catch(BadSSOPayloadException $e) {
            // TODO: missing data from discourse in payload - handle...
            abort(400);
        }

        // store the nonce and return url in a session so
        // the user cannot access or tamper with it at any
        // point during authentication
        $request->session()->put([
            'discourse_nonce'   => $payload['nonce'],
            'discourse_return'  => $payload['return_sso_url'],
        ]);      

        return view('login');
    }

    public function login(Request $request, Validation $validation) {
        $this->validateNonceSession($request);

        $validator = $validation->make($request->all(), [
            'email'     => 'required',
            'password'  => 'required',
        ]);

        $validator->after(function($validator) use($request) {
            $credentials = [
                'email'     => $request->get('email'),
                'password'  => $request->get('password'),
            ];
            if($this->auth->attempt($credentials, true) === false) {
                $validator->errors()->add('error', 'Email or password is incorrect');
            }
        });

        if($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator->errors())
                ->withInput();
        }

        return $this->dispatchToDiscourse(
            $request,
            $request->get('email'),
            $this->auth->id()
        );
    }

    /**
     * Logs out the current PCB account
     *
     * @param Request $request
     * @return void
     */
    public function logoutFromDiscourse(Request $request) {
        $this->auth->logout();
        return redirect()->route('front.home');
    }

    /**
     * Logs out the current PCB account and
     * its associated Discourse account
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request) {
        if(!$this->auth->check()) {
            return redirect()->route('front.home');
        }

        $externalId = $this->auth->id();
        $response = $this->client->get('http://forums.projectcitybuild.com/users/by-external/'.$externalId.'.json');
        $result = json_decode($response->getBody(), true);

        $id   = $result['user']['id'];
        $user = $result['user']['username'];
        $discourseKey = env('DISCOURSE_API_KEY');
        $this->client->post('http://forums.projectcitybuild.com/admin/users/'.$id.'/log_out?api_key='.$discourseKey.'&api_username='.$user);


        $this->auth->logout();
        
        return redirect()->route('front.home');
    }

    private function validateNonceSession(Request $request) {
        $session = $request->session();
        $nonce   = $session->get('discourse_nonce');
        $return  = $session->get('discourse_return');

        if($nonce === null || $return === null) {
            // TODO: payload data missing - handle
            abort(400);
        }
    }

    private function dispatchToDiscourse(
        Request $request, 
        string $email,
        $accountId
    ) {
        $session = $request->session();
        $nonce   = $session->get('discourse_nonce');
        $return  = $session->get('discourse_return');

        // generate new payload to send to discourse
        $payload = $this->discourseAuthService->makePayload([
            'nonce' => $nonce,
            'email' => $email,
            'external_id' => $accountId,
            'require_activation' => false,
        ]);
        $signature = $this->discourseAuthService->getSignedPayload($payload);

        // attach parameters to return url
        $endpoint = $this->discourseAuthService->getRedirectUrl($return, $payload, $signature);

        $session->remove('discourse_nonce');
        $session->remove('discourse_return');

        return redirect()->to($endpoint);
    }

    
    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request) {
        $providerUser = Socialite::driver('google')->user();

        if($providerUser->getEmail() === null) {
            // TODO: no email, cannot proceed
            abort(400);
        }

        $account = $this->accountLinkService->getOrCreateAccount('google', $providerUser);
        if($account === null) {
            //
            abort(400);
        }

        $this->validateNonceSession($request);

        $this->auth->setUser($account);

        return $this->dispatchToDiscourse(
            $request,
            $providerUser->getEmail(),
            $account->getKey()
        );
    }

    public function redirectToTwitter() {
        return Socialite::driver('twitter')->redirect();
    }

    public function handleTwitterCallback(Request $request) {
        $providerUser = Socialite::driver('twitter')->user();

        if($providerUser->getEmail() === null) {
            // TODO: no email, cannot proceed
            abort(400);
        }

        $account = $this->accountLinkService->getOrCreateAccount('twitter', $providerUser);
        if($account === null) {
            //
            abort(400);
        }

        $this->validateNonceSession($request);

        $this->auth->setUser($account);

        return $this->dispatchToDiscourse(
            $request,
            $providerUser->getEmail(),
            $account->getKey()
        );
    }

    public function redirectToFacebook() {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback(Request $request) {
        $providerUser = Socialite::driver('facebook')->user();

        if($providerUser->getEmail() === null) {
            // TODO: no email, cannot proceed
            abort(400);
        }

        $account = $this->accountLinkService->getOrCreateAccount('facebook', $providerUser);
        if($account === null) {
            //
            abort(400);
        }

        $this->validateNonceSession($request);

        $this->auth->setUser($account);

        return $this->dispatchToDiscourse(
            $request,
            $providerUser->getEmail(),
            $account->getKey()
        );
    }
}
