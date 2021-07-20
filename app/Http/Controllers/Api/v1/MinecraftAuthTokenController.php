<?php

namespace App\Http\Controllers\Api\v1;

use App\Entities\Accounts\Resources\AccountResource;
use App\Entities\Players\Models\MinecraftAuthCode;
use App\Entities\Players\Models\MinecraftPlayer;
use App\Exceptions\Http\BadRequestException;
use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\UnauthorisedException;
use App\Http\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class MinecraftAuthTokenController extends ApiController
{
    /**
     * Requests an URL that the user can click to link their PCB account.
     *
     *
     * @return void
     */
    public function store(Request $request)
    {
        $this->validateRequest($request->all(), [
            'minecraft_uuid' => 'bail|required|string', // TODO: override UUID rule to allow UUIDs without hyphens
        ]);

        $uuid = $request->get('minecraft_uuid');
        $uuid = str_replace('-', '', $uuid);

        $existingPlayer = MinecraftPlayer::where('uuid', $uuid)->first();

        if ($existingPlayer === null) {
            $existingPlayer = MinecraftPlayer::create([
                'uuid' => $uuid,
                'account_id' => null,
            ]);
        }

        // Re-authenticating a Minecraft account when it's already authenticated is not currently
        // supported, as this might cause some unexpected results
        if ($existingPlayer->account_id !== null) {
            throw new ForbiddenException('already_authenticated', 'This UUID has already been authenticated');
        }

        // Invalidate any existing auth codes since only 1 should be alive
        MinecraftAuthCode::where('player_minecraft_id', $existingPlayer->getKey())->delete();

        $authCode = MinecraftAuthCode::create([
            'player_minecraft_id' => $existingPlayer->getKey(),
            'uuid' => $uuid,
            'token' => Str::uuid()->toString(),
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        $authCompletionRoute = route('front.auth.minecraft.token', ['token' => $authCode->token]);

        return [
            'data' => [
                'url' => $authCompletionRoute,
            ],
        ];
    }

    /**
     * Returns the PCB groups that the given UUID belongs to.
     *
     *
     * @return void
     */
    public function show(Request $request, string $minecraftUUID)
    {
        $uuid = str_replace('-', '', $minecraftUUID);

        if (empty($uuid)) {
            throw new BadRequestException('bad_input', 'minecraft_uuid cannot be empty');
        }

        $existingPlayer = MinecraftPlayer::where('uuid', $uuid)->first();

        if ($existingPlayer === null || $existingPlayer->account === null) {
            throw new UnauthorisedException('account_not_linked', 'This UUID has not been linked to a PCB account. Please complete the authorization flow first');
        }

        // Update last seen at time
        $existingPlayer->touchLastSyncedAt();

        // Force load groups
        $existingPlayer->account->groups;

        return [
            'data' => new AccountResource($existingPlayer->account),
        ];
    }
}
