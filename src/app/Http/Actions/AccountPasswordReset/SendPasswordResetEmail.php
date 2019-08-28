<?php

namespace App\Http\Actions\AccountPasswordReset;

use App\Helpers\TokenHelpers;
use App\Entities\Eloquent\Accounts\Models\AccountPasswordReset;
use App\Entities\Eloquent\Accounts\Models\Account;
use App\Entities\Eloquent\Accounts\Notifications\AccountPasswordResetNotification;
use Illuminate\Support\Carbon;

final class SendPasswordResetEmail
{
    public function execute(Account $account, string $email)
    {
        $passwordReset = AccountPasswordReset::updateOrCreate([
            'email' => $email,
        ], [
            'token' => TokenHelpers::generateToken(),
            'created_at' => Carbon::now(),
        ]);

        $account->notify(new AccountPasswordResetNotification($passwordReset));
    }
}