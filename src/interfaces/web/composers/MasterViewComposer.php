<?php
namespace Interfaces\Web\Composers;

use Illuminate\View\View;
use Domains\Modules\Servers\Repositories\ServerCategoryRepository;

class MasterViewComposer
{
    private $serverCategoryRepository;
    
    public function __construct(ServerCategoryRepository $serverCategoryRepository)
    {
        $this->serverCategoryRepository = $serverCategoryRepository;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $servers = $this->serverCategoryRepository->getAllVisible();
        $view->with('serverCategories', $servers);
    }
}
