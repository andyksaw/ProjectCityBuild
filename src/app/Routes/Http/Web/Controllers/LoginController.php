<?php

namespace App\Routes\Http\Web\Controllers;

use App\Routes\Http\Web\WebController;
use Illuminate\Validation\Factory as Validation;
use App\Modules\Forums\Services\Authentication\DiscourseAuthService;
use Illuminate\Http\Request;

class LoginController extends WebController {

    /**
     * @var DiscourseAuthService
     */
    private $discourseAuthService;

    public function __construct(DiscourseAuthService $discourseAuthService) {
        $this->discourseAuthService = $discourseAuthService;
    }

    public function showLoginView(Request $request) {
        // login route should have a valid payload in the url
        // generated by discourse when being redirected here
        $payload    = $request->get('sso');
        $signature  = $request->get('sig');

        if($payload === null || $signature === null) {
            // TODO: handle no payload present
            // abort(401);
        }

        $validPayload = $this->discourseAuthService->isValidPayload($payload, $signature);
        if(!$validPayload) {
            // TODO: handle bad payload
            // abort(403);
        }

        return view('login');
    }

    public function login(Request $request, Validation $validation) {
        $validator = $validation->make($request->all(), [

        ]);
    }

}
