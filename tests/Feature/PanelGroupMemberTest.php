<?php

namespace Tests\Feature;

use App\Entities\Accounts\Models\Account;
use App\Entities\Groups\Models\Group;
use Tests\TestCase;

class PanelGroupMemberTest extends TestCase
{
    public function testGroupListOnlyShowsGroupMembers()
    {
        $groupA = Group::factory()->create();
        $groupB = Group::factory()->create();
        $accountA = Account::factory()->create();
        $accountA->groups()->attach($groupA);
        $accountB = Account::factory()->create();
        $accountB->groups()->attach($groupB);
        $this->withoutExceptionHandling();
        $this->actingAs($this->adminAccount())
            ->get(route('front.panel.groups.accounts', $groupA))
            ->assertSee($accountA->username)
            ->assertDontSee($accountB->username);
    }
}
