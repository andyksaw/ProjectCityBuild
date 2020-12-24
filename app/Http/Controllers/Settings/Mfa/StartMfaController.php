<?php


namespace App\Http\Controllers\Settings\Mfa;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class StartMfaController extends \App\Http\WebController
{
    /**
     * @var Google2FA
     */
    private $google2FA;

    /**
     * EnableTotpController constructor.
     * @param Google2FA $google2FA
     */
    public function __construct(Google2FA $google2FA)
    {
        $this->google2FA = $google2FA;
    }


    public function __invoke(Request $request)
    {
        if ($request->user()->is_totp_enabled) {
            abort(403);
        }

        $secret = $this->google2FA->generateSecretKey();
        $backupCode = Str::random(32);
        $request->user()->totp_secret = $secret;
        $request->user()->totp_backup_code = $backupCode;
        $request->user()->save();

        return redirect()->route('front.account.security.setup');
    }
}