<?php

namespace App\Routes\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Bans\Models\GameBan;
use App\Modules\Bans\Models\GameUnban;
use App\Modules\Servers\Repositories\ServerRepository;
use App\Modules\Donations\Models\Donation;
use DB;
use Cache;
use Carbon\Carbon;
use App\Modules\Servers\Services\PlayerFetching\Api\Mojang\MojangApiService;
use App\Modules\Players\Models\MinecraftPlayer;
use App\Modules\Players\Models\MinecraftPlayerAlias;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:db {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports db data from the current (soon to be old) live website';

    private $aliasRepository;
    private $serverRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ServerRepository $serverRepository)
    {
        parent::__construct();

        $this->serverRepository = $serverRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $module = $this->argument('module');

        switch($module) {
            case 'bans':
                return $this->importBans();
            case 'donations':
                return $this->importDonations();
            default:
                $this->error('Invalid import module name. Valid: [bans, donations]');
                break;
        }
    }

    private function importBans() {
        $this->info('[Ban data importer]');
        $this->warn('Warning: No check for existence is made before importing bans! This should only be run once in production');

        $this->info('Importing game players...');
        $players = DB::connection('mysql_import_pcbridge')
            ->table('ban_players')
            ->select('id', 'alias', 'uuid')
            ->get();

        $playerBar = $this->output->createProgressBar(count($players));
        $playerList = [];

        $playerIdToUuid = [];
        $playerIdToAlias = [];

        DB::beginTransaction();
        try {
            foreach($players as $player) {
                if($player->alias === 'CONSOLE' || $player->uuid === 'CONSOLE') {
                    $playerBar->advance();
                    continue;
                }

                $userAlias = $this->aliasRepository->getAlias(UserAliasTypeEnum::MINECRAFT_UUID, $player->uuid);
                if(is_null($userAlias)) {
                    $gameUser = GameUser::create([
                        'user_id' => null,
                    ]);

                    $userAlias = UserAlias::create([
                        'user_alias_type_id' => UserAliasTypeEnum::MINECRAFT_UUID,
                        'alias' => $player->uuid,
                        'game_user_id' => $gameUser->game_user_id,
                    ]);

                    UserAlias::create([
                        'user_alias_type_id' => UserAliasTypeEnum::MINECRAFT_NAME,
                        'alias' => $player->alias,
                        'game_user_id' => $gameUser->game_user_id,
                    ]);

                    $playerIdToUuid[$userAlias->game_user_id] = $userAlias->user_alias_id;
                    $playerIdToAlias[$userAlias->game_user_id] = $player->alias;
                }
                $playerList[$player->id] = $userAlias->game_user_id;
                
                $playerBar->advance();
            }
            DB::commit();

        } catch(\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        


        $this->info('Importing ban records...');

        $bans = DB::connection('mysql_import_pcbridge')
            ->table('ban_records_bans')
            ->select('*')
            ->get();

        $bar = $this->output->createProgressBar(count($bans));

        $minecraftServer = $this->serverRepository->getServerByName('Survival / Creative [24/7]');
        $banIds = [];
        
        DB::beginTransaction();
        try {
            foreach($bans as $oldBan) {
                $newPlayerId = $playerList[$oldBan->player_id];

                $newBan = GameBan::create([
                    'server_id' => $minecraftServer->server_id,
                    'player_game_user_id' => $newPlayerId,
                    'staff_game_user_id' => $oldBan->staff_id == 2 || $oldBan->staff_id == 4956 ? null : $playerList[$oldBan->staff_id],
                    'banned_alias_id' => $playerIdToUuid[$newPlayerId],
                    'player_alias_at_ban' => $playerIdToAlias[$newPlayerId],
                    'reason' => $oldBan->reason,
                    'is_active' => $oldBan->is_banned,
                    'is_global_ban' => true,
                    'expires_at' => $oldBan->unban_on ? Carbon::createFromTimestamp($oldBan->unban_on) : null,
                    'created_at' => Carbon::createFromTimestamp($oldBan->timestamp),
                    'updated_at' => Carbon::createFromTimestamp($oldBan->timestamp),
                ]);
                $banIds[$oldBan->id] = $newBan->game_ban_id;
                
                $bar->advance();
            }

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            throw $e;
        }


        $this->info('Importing unban records...');

        $unbans = DB::connection('mysql_import_pcbridge')
            ->table('ban_records_unbans')
            ->select('*')
            ->get();

        $bar = $this->output->createProgressBar(count($unbans));

        DB::beginTransaction();
        try {
            foreach($unbans as $oldUnban) {
                GameUnban::create([
                    'game_ban_id' => $banIds[$oldUnban->ban_id],
                    'staff_game_user_id' => $oldUnban->staff_id == 2 || $oldUnban->staff_id == 4956 ? null : $playerList[$oldUnban->staff_id],
                    'created_at' => Carbon::createFromTimestamp($oldUnban->timestamp),
                ]);

                $ban = GameBan::find($banIds[$oldUnban->ban_id]);
                $ban->updated_at = Carbon::createFromTimestamp($oldUnban->timestamp);
                $ban->save();

                $bar->advance();
            }

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $this->info('Import complete');
    }

    private function importDonations() {
        $this->info('[Donation data importer]');
        $this->warn('Warning: No check for existence is made before importing donations! This should only be run once in production');


        $uuidFetcher = resolve(MojangApiService::class);

        $lastDonationId = Donation::orderBy('donation_id', 'desc')->first();
        $lastDonationId = $lastDonationId ? $lastDonationId->donation_id : 0;

        $this->info('Fetching old records...');
        $donations = DB::connection('mysql_import_pcb')
            ->table('donators')
            ->select('*')
            ->where('id', '>', $lastDonationId)
            ->get();


        $this->info('Importing game players...');
        $bar = $this->output->createProgressBar(count($donations));
        foreach($donations as $donation) {
            $expiryDate = Carbon::createFromFormat('Y-m-d', $donation->end_date);
            $createDate = Carbon::createFromFormat('Y-m-d', $donation->start_date);

            $updateDate = $createDate;
            $isActive = true;

            $hasExpired = !$donation->lifetime && $expiryDate <= Carbon::now();
            if($hasExpired) {
                $updateDate = $expiryDate;
                $isActive = false;
            }

            $username = $donation->username;
            $matchingForumUser = DB::connection('mysql_forums')
                    ->table('members')
                    ->select('id_member', 'real_name', 'member_name')
                    ->where('real_name', $username)
                    ->orWhere('member_name', $username)
                    ->first();

            // check for a matching forum username
            $uuid = null;
            if(is_null($matchingForumUser)) {
                
                // otherwise grab their uuid and try search by that
                $uuid = $uuidFetcher->getUuidOf($username, $createDate->getTimestamp() * 1000);
        
                // if no uuid at the donation time, check for the original owner of the username
                if($uuid === null) {
                    $uuid = $uuidFetcher->getOriginalOwnerUuidOf($username);
                }

                // if uuid found, check if their current alias has a forum account
                if($uuid !== null) {
                    $currentAlias = $uuid->getAlias();
                    $this->info($currentAlias);

                    $forumUser = DB::connection('mysql_forums')
                        ->table('members')
                        ->select('id_member', 'real_name')
                        ->where('real_name', 'LIKE', $currentAlias)
                        ->orWhere('member_name', $currentAlias)
                        ->first();

                    if($forumUser) {
                        var_dump($forumUser);
                        $matchingForumUser = $forumUser;
                    }
                }
            }

            if(is_null($matchingForumUser)) {
                throw new \Exception('No forum account for ' . $username);                    
            }

            // if no uuid was fetched, grab their uuid from mojang
            if($uuid === null) {
                $uuid = $uuidFetcher->getUuidOf($matchingForumUser->real_name);
                if($uuid === null) {
                    $uuid = $uuidFetcher->getOriginalOwnerUuidOf($matchingForumUser->real_name);
                }
            }
            if($uuid === null) {
                throw new \Exception('No UUID for ' . $matchingForumUser->real_name . ' ('.$username.')');
            }

            $existingPlayer = MinecraftPlayer::where('uuid', $uuid->getUuid())->first();
            if($existingPlayer === null) {
                $player = MinecraftPlayer::create([
                    'uuid' => $uuid->getUuid(),
                    'playtime' => 0,
                    'last_seen_at' => Carbon::now(),
                ]);
                MinecraftPlayerAlias::create([
                    'player_minecraft_id' => $player->player_minecraft_id,
                    'alias' => $uuid->getAlias(),
                ]);
            }
            

            Donation::create([
                'forum_user_id' => $matchingForumUser->id_member,
                'amount' => $donation->amount,
                'perks_end_at' => $donation->lifetime ? null : $expiryDate,
                'prev_rank_id' => $donation->previous_rank > 0 ? $donation->previous_rank : null,
                'is_lifetime_perks' => $donation->lifetime,
                'is_active' => $isActive,
                'created_at' => $createDate,
                'updated_at' => $updateDate,
            ]);

            $bar->advance();
            }

        $this->info('Import complete');
    }
}
