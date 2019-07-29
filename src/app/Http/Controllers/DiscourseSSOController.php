<?php


namespace App\Http\Controllers;


use App\Entities\Environment;
use App\Http\WebController;
use App\Library\Discourse\Authentication\DiscourseLoginHandler;
use App\Library\Discourse\Exceptions\BadSSOPayloadException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiscourseSSOController extends WebController
{
    /**
     * @var DiscourseLoginHandler
     */
    private $discourseLoginHandler;

    /**
     * DiscourseSSOController constructor.
     * @param DiscourseLoginHandler $discourseLoginHandler
     */
    public function __construct(DiscourseLoginHandler $discourseLoginHandler)
    {
        $this->discourseLoginHandler = $discourseLoginHandler;
    }

    public function create(Request $request)
    {
        $account  = $request->user();

        try {
            $endpoint = $this->discourseLoginHandler->getRedirectUrl(
                $request,
                $account->getKey(),
                $account->email,
                $account->username
            );
        } catch (BadSSOPayloadException $e)
        {
            Log::debug('Missing nonce or return key in session', ['session' => $request->session()]);
            throw $e;
        }

        return redirect()->to($endpoint);

    }
}
