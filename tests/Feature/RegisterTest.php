<?php

namespace Tests\Feature;

use App\Entities\Accounts\Models\Account;
use App\Entities\Accounts\Notifications\AccountActivationNotification;
use App\Entities\Groups\Models\Group;
use App\Library\Recaptcha\RecaptchaRule;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RecaptchaRule::enable(false);
    }

    private function withRequiredFormFields(Account $account): array
    {
        return array_merge($account->toArray(), [
            'password_confirm' => 'password',
            'g-recaptcha-response' => Str::random(),
        ]);
    }

    public function testCannotSeeRegisterSignedIn()
    {
        $this->actingAs(Account::factory()->create())
            ->get(route('front.register'))
            ->assertRedirect(route('front.account.settings'));
    }

    public function testUserCanRegister()
    {
        $unactivatedAccount = Account::factory()
            ->passwordUnhashed()
            ->unactivated()
            ->make();

        $this->post(route('front.register.submit'), $this->withRequiredFormFields($unactivatedAccount))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('accounts', [
            'email' => $unactivatedAccount->email,
            'username' => $unactivatedAccount->username,
            'activated' => false,
        ]);
    }

    public function testRecaptchaFieldIsRequired()
    {
        RecaptchaRule::enable(true);

        $unactivatedAccount = Account::factory()
            ->passwordUnhashed()
            ->unactivated()
            ->make();

        $this->post(route('front.register.submit'), $this->withRequiredFormFields($unactivatedAccount))
            ->assertSessionHasErrors('g-recaptcha-response');
    }

    public function testRecaptchaFieldIsValidated()
    {
        RecaptchaRule::enable(true);

        $unactivatedAccount = Account::factory()
            ->passwordUnhashed()
            ->unactivated()
            ->make();

        $this->post(route('front.register.submit'), $this->withRequiredFormFields($unactivatedAccount))
            ->assertSessionHasErrors('g-recaptcha-response');
    }

    public function testUserCannotRegisterWithSameEmailAsOtherAccount()
    {
        $existingAccount = Account::factory()->create();

        $newAccount = Account::factory()
            ->passwordUnhashed()
            ->make(['email' => $existingAccount->email]);

        $this->post(route('front.register.submit'), $this->withRequiredFormFields($newAccount))
            ->assertSessionHasErrors();
    }

    public function testUserCannotRegisterWithSameUsernameAsOtherAccount()
    {
        $existingAccount = Account::factory()->create();

        $newAccount = Account::factory()
            ->passwordUnhashed()
            ->make(['username' => $existingAccount->username]);

        $this->post(route('front.register.submit'), $this->withRequiredFormFields($newAccount))
            ->assertSessionHasErrors();
    }

    public function testAssertPasswordIsHashed()
    {
        $unactivatedAccount = Account::factory()
            ->passwordUnhashed()
            ->make();

        $this->post(route('front.register.submit'), $this->withRequiredFormFields($unactivatedAccount))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('accounts', [
            'email' => $unactivatedAccount->email,
            'password' => $unactivatedAccount->password,
        ]);
    }

    public function testNewMemberIsPutInDefaultGroup()
    {
        $memberGroup = Group::create([
            'name' => 'member',
            'is_default' => 1,
        ]);

        $unactivatedAccount = Account::factory()
            ->passwordUnhashed()
            ->unactivated()
            ->make();

        $this->post(route('front.register.submit'), $this->withRequiredFormFields($unactivatedAccount))
            ->assertSessionHasNoErrors();

        $account = Account::where('email', $unactivatedAccount['email'])->firstOrFail();

        $this->assertDatabaseHas('groups_accounts', [
            'group_id' => $memberGroup->group_id,
            'account_id' => $account->account_id,
        ]);
    }

    public function testUserIsSentVerificationMail()
    {
        Notification::fake();

        $unactivatedAccount = Account::factory()
            ->passwordUnhashed()
            ->unactivated()
            ->make();

        $this->post(route('front.register.submit'), $this->withRequiredFormFields($unactivatedAccount))
            ->assertSuccessful()
            ->assertSessionDoesntHaveErrors();

        Notification::assertSentTo(Account::first(), AccountActivationNotification::class);
    }

    public function testUserCanVerifyEmail()
    {
        $unactivatedAccount = Account::factory()->unactivated()->create();

        $this->get($unactivatedAccount->getActivationUrl())
            ->assertSuccessful();

        $this->assertEquals(true, Account::first()->activated);
    }

    public function testUserIsRedirectedToIntentAfterVerification()
    {
        Session::put('url.intended', '/my/path');

        $unactivatedAccount = Account::factory()->unactivated()->create();

        $this->get($unactivatedAccount->getActivationUrl())
            ->assertRedirect('/my/path');
    }
}
