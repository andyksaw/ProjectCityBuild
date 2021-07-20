<?php

namespace App\Http\Controllers\Api\v1;

use App\Entities\Groups\Models\Group;
use App\Entities\Groups\Resources\GroupResource;
use App\Http\ApiController;

final class GroupApiController extends ApiController
{
    public function getAll()
    {
        $groups = Group::get();

        return GroupResource::collection($groups);
    }
}
