<?php

namespace App\Routes\Http\Web\Controllers;

use App\Modules\Discourse\Services\Authentication\DiscourseAuthService;
use App\Modules\Forums\Exceptions\BadSSOPayloadException;
use App\Modules\Accounts\Services\AccountSocialLinkService;
use App\Routes\Http\Web\WebController;
use Illuminate\Validation\Factory as Validation;
use Illuminate\Contracts\Auth\Guard as Auth;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Modules\Accounts\Repositories\AccountRepository;
use Illuminate\Support\Facades\URL;
use App\Modules\Accounts\Services\AccountSocialAuthService;
use App\Modules\Accounts\Execeptions\UnsupportedAuthProviderException;
use App\Modules\Accounts\Services\Login\AccountManualLoginExecutor;
use App\Modules\Accounts\Services\Login\AccountLoginService;
use App\Modules\Accounts\Services\Login\AccountSocialLoginExecutor;

class LoginController extends WebController {

    /**
     * @var DiscourseAuthService
     */
    private $discourseAuthService;

    /**
     * @var AccountSocialAuthService
     */
    private $socialAuthService;

    /**
     * @var AccountSocialLinkService
     */
    private $accountLinkService;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

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
        AccountSocialAuthService $socialAuthService,
        AccountSocialLinkService $accountLinkService,
        AccountRepository $accountRepository,
        Auth $auth,
        Client $client
    ) {
        $this->discourseAuthService = $discourseAuthService;
        $this->socialAuthService = $socialAuthService;
        $this->accountLinkService = $accountLinkService;
        $this->accountRepository = $accountRepository;
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
            abort(400);
        }

        // ensure that the payload has all the necessary
        // data required to create a new payload after
        // authentication
        $payload = null;
        try {
            $payload = $this->discourseAuthService->unpackPayload($sso);
        } catch(BadSSOPayloadException $e) {
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

    /**
     * Manual login with email and password via form post
     *
     * @param Request $request
     * @param AccountLoginService $loginService
     * @param AccountManualLoginExecutor $executor
     * @return void
     */
    public function login(Request $request, AccountManualLoginExecutor $loginHandler) {
        return $loginHandler->login($request);
    }

    /**
     * Logs out the current PCB account
     * 
     * (called from Discourse)
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
     * (called from this site)
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request) {
        if(!$this->auth->check()) {
            return redirect()->route('front.home');
        }

        $externalId = $this->auth->id();
        $response = $this->client->get('https://forums.projectcitybuild.com/users/by-external/'.$externalId.'.json');
        $result = json_decode($response->getBody(), true);

        $id   = $result['user']['id'];
        $user = $result['user']['username'];
        $discourseKey = env('DISCOURSE_API_KEY');
        $this->client->post('https://forums.projectcitybuild.com/admin/users/'.$id.'/log_out?api_key='.$discourseKey.'&api_username='.$user);


        $this->auth->logout();
        
        return redirect()->route('front.home');
    }

    
    public function redirectToFacebook() {
        return $this->redirectToProvider('facebook');
    }
    public function redirectToGoogle() {
        return $this->redirectToProvider('google');
    }
    public function redirectToTwitter() {
        return $this->redirectToProvider('twitter');
    }

    private function redirectToProvider(string $providerName) {
        return $this->socialAuthService
            ->setProvider($providerName)
            ->getProviderUrl();
    }

    public function handleFacebookCallback(Request $request, AccountSocialLoginExecutor $loginHandler) {
        return $loginHandler->setProvider('facebook')->login($request);
    }
    public function handleGoogleCallback(Request $request, AccountSocialLoginExecutor $loginHandler) {
        return $loginHandler->setProvider('google')->login($request);
    }
    public function handleTwitterCallback(Request $request, AccountSocialLoginExecutor $loginHandler) {
        return $loginHandler->setProvider('twitter')->login($request);
    }

    public function createSocialAccount(Request $request, AccountSocialLoginExecutor $loginHandler) {
        $email      = $request->get('email');
        $id         = $request->get('id');
        $provider   = $request->get('provider');

        if($email === null) {
            abort(400, 'Missing social email');
        }
        if($id === null) {
            abort(400, 'Missing social id');
        }
        if($provider === null) {
            abort(400, 'Missing social provider');
        }

        $account = $this->accountLinkService->createAccount($provider, $email, $id);

        return $loginHandler
            ->setProvider($provider)
            ->setAccount($account)
            ->login($request);
    }

}
