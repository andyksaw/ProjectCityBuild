<?php
namespace Domains\Library\OAuth\Adapters\Google;

use Domains\Library\OAuth\Adapters\OAuthTwoStepProvider;
use Domains\Library\OAuth\OAuthUser;
use Domains\Library\OAuth\OAuthToken;

class GoogleOAuthAdapter extends OAuthTwoStepProvider
{
    /**
     * @inheritDoc
     */
    protected $authUrl = 'https://accounts.google.com/o/oauth2/auth';

    /**
     * @inheritDoc
     */
    protected $tokenUrl = 'https://accounts.google.com/o/oauth2/token';

    /**
     * @inheritDoc
     */
    protected $userUrl = 'https://www.googleapis.com/plus/v1/people/me';

    
    /**
     * @inheritDoc
     */
    protected function getAuthRequestParams(string $redirectUri) : array
    {
        return [
            'client_id'     => config('services.google.client_id'),
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid profile email',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getTokenRequestParams(string $redirectUri, string $authCode) : array
    {
        return [
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'grant_type'    => 'authorization_code',
            'code'          => $authCode,
            'redirect_uri'  => $redirectUri,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function makeUser(array $json) : OAuthUser
    {
        $googleUser = GoogleOAuthUser::fromJSON($json);

        return new OAuthUser('google', 
                             $googleUser->getFirstEmail(), 
                             $googleUser->getDisplayName(), 
                             $googleUser->getId());
    }

    /**
     * @inheritDoc
     */
    protected function makeToken() : OAuthToken
    {
        return new OAuthToken($json['access_token'],
                              $json['token_type'],
                              $json['expires_in'],
                              $json['id_token'],
                              $json['scope']);
    }
}