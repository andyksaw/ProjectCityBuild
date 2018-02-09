<?php

namespace App\Routes\Http\Controllers;

use Illuminate\Support\Facades\View;
use App\Modules\Forums\Services\Retrieve\ForumRetrieveInterface;
use App\Modules\Donations\Services\DonationStatsService;
use App\Modules\Forums\Services\SMF\Smf;
use Carbon\Carbon;
use Storage;
use App\Routes\Http\Web\WebController;

class HomeController extends WebController
{
    /**
     * @var ForumRetrieveInterface
     */
    private $forumRetrieveService;

    /**
     * @var DonationStatsService
     */
    private $donationStatsService;

    
    public function __construct(
        ForumRetrieveInterface $forumRetrieveService, 
        DonationStatsService $donationStatsService
    ) {
        $this->forumRetrieveService = $forumRetrieveService;
        $this->donationStatsService = $donationStatsService;
    }

    public function getView(Smf $smf) {
        $user = $smf->getUser();
        $groups = $user->getUserGroupsFromDatabase();

        $announcements = $this->forumRetrieveService->getAnnouncements($groups);
        $donations = $this->donationStatsService->getAnnualPercentageStats();

        return view('home', [
            'announcements' => $announcements,
            'donations'     => $donations,
        ]);
    }
}