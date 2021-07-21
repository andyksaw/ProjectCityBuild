<?php

use App\Entities\Environment;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Force all web routes to load over https
if (Environment::isProduction()) {
    URL::forceScheme('https');
}

/**
 * URL redirects.
 */
Route::redirect('terms', 'https://forums.projectcitybuild.com/t/community-rules/22928')->name('terms');
Route::redirect('privacy', 'https://forums.projectcitybuild.com/privacy')->name('privacy');
Route::redirect('wiki', 'https://wiki.projectcitybuild.com')->name('wiki');
Route::redirect('maps', 'https://maps.pcbmc.co')->name('maps');
Route::redirect('3d-maps', 'https://3d.pcbmc.co')->name('3d-maps');

/**
 * Style guide.
 */
Route::view('ui', 'stylesheet');

Route::get('/', [
    'as' => 'front.home',
    'uses' => 'HomeController@index',
]);

Route::prefix('donate')->group(function () {
    Route::get('/', [
        'as' => 'front.donate',
        'uses' => 'DonationController@index',
    ]);
    Route::get('success', [
        'as' => 'front.donate.success',
        'uses' => 'DonationController@success',
    ]);
});

Route::get('sso/discourse', [
    'as' => 'front.sso.discourse',
    'uses' => 'DiscourseSSOController@create',
])->middleware('auth');

Route::prefix('login')->group(function () {
    Route::get('/', [
        'as' => 'front.login',
        'uses' => 'LoginController@create',
    ])->middleware('guest');

    Route::post('/', [
        'as' => 'front.login.submit',
        'uses' => 'LoginController@store',
    ])->middleware('guest');

    Route::get('/reauth', [
        'as' => 'password.confirm',
        'uses' => 'ReauthController@show',
    ])->middleware('auth');

    Route::post('/reauth', [
        'as' => 'password.confirm',
        'uses' => 'ReauthController@process',
    ])->middleware(['auth', 'throttle:6,1']);

    Route::get('/mfa', [
        'as' => 'front.login.mfa',
        'uses' => 'MfaLoginGateController@create',
    ])->middleware(['auth', 'active-mfa']);

    Route::post('/mfa', [
        'as' => 'front.login.mfa',
        'uses' => 'MfaLoginGateController@store',
    ])->middleware(['auth', 'active-mfa', 'throttle:6,1']);

    Route::get('/mfa/recover', [
        'as' => 'front.login.mfa-recover',
        'uses' => 'MfaBackupController@show',
    ])->middleware(['auth', 'active-mfa']);

    Route::delete('/mfa/recover', [
        'as' => 'front.login.mfa-recover',
        'uses' => 'MfaBackupController@destroy',
    ])->middleware(['auth', 'active-mfa', 'throttle:6,1']);
});

Route::prefix('password-reset')->group(function () {
    Route::get('/', [
        'as' => 'front.password-reset.create',
        'uses' => 'PasswordResetController@create',
    ]);

    Route::post('/', [
        'as' => 'front.password-reset.store',
        'uses' => 'PasswordResetController@store',
    ]);

    Route::get('edit', [
        'as' => 'front.password-reset.edit',
        'uses' => 'PasswordResetController@edit',
    ])->middleware('signed');

    Route::patch('edit', [
        'as' => 'front.password-reset.update',
        'uses' => 'PasswordResetController@update',
    ]);
});

Route::prefix('register')->group(function () {
    Route::get('/', [
        'as' => 'front.register',
        'uses' => 'RegisterController@showRegisterView',
    ])->middleware('guest');

    Route::post('/', [
        'as' => 'front.register.submit',
        'uses' => 'RegisterController@register',
    ])->middleware('guest');

    Route::get('activate', [
        'as' => 'front.register.activate',
        'uses' => 'RegisterController@activate',
    ])->middleware('signed');
});

Route::get('logout/discourse', [
    'as' => 'front.logout.pcb',
    'uses' => 'LoginController@logoutFromDiscourse',
]);
Route::get('logout', [
    'as' => 'front.logout',
    'uses' => 'LoginController@logout',
]);

Route::group(['prefix' => 'account', 'middleware' => 'auth', 'namespace' => 'Settings'], function () {
    Route::prefix('settings')->group(function () {
        Route::get('/', [
            'as' => 'front.account.settings',
            'uses' => 'AccountSettingController@showView',
        ]);

        Route::post('email/verify', [
            'as' => 'front.account.settings.email',
            'uses' => 'AccountSettingController@sendVerificationEmail',
        ]);

        Route::get('email/confirm', [
            'as' => 'front.account.settings.email.confirm',
            'uses' => 'AccountSettingController@showConfirmForm',
        ])->middleware('signed');

        Route::post('email/confirm', [
            'as' => 'front.account.settings.email.confirm.save',
            'uses' => 'AccountSettingController@confirmEmailChange',
        ]);

        Route::post('password', [
            'as' => 'front.account.settings.password',
            'uses' => 'AccountSettingController@changePassword',
        ]);

        Route::post('username', [
            'as' => 'front.account.settings.username',
            'uses' => 'AccountSettingController@changeUsername',
        ]);
    });

    Route::prefix('donations')->group(function () {
        Route::get('/', [
            'as' => 'front.account.donations',
            'uses' => 'AccountDonationController@index',
        ]);
    });

    Route::prefix('games')->group(function () {
        Route::get('/', [
            'as' => 'front.account.games',
            'uses' => 'AccountGameAccountController@index',
        ]);

        Route::delete('/{minecraft_player}', [
            'as' => 'front.account.games.delete',
            'uses' => 'AccountGameAccountController@destroy',

        ]);
    });

    Route::prefix('security')->group(function () {
        Route::get('/', [
            'as' => 'front.account.security',
            'uses' => 'AccountSecurityController@show',
        ]);

        Route::post('/mfa', [
            'as' => 'front.account.security.start',
            'uses' => 'Mfa\\StartMfaController',
        ]);

        Route::get('/mfa/setup', [
            'as' => 'front.account.security.setup',
            'uses' => 'Mfa\\SetupMfaController',
        ]);

        Route::post('/mfa/finish', [
            'as' => 'front.account.security.finish',
            'uses' => 'Mfa\\FinishMfaController',
        ]);

        Route::get('/mfa/disable', [
            'as' => 'front.account.security.disable',
            'uses' => 'Mfa\\DisableMfaController@show',
        ])->middleware('password.confirm');

        Route::delete('/mfa/disable', [
            'as' => 'front.account.security.disable',
            'uses' => 'Mfa\\DisableMfaController@destroy',
        ])->middleware('password.confirm');

        Route::get('/mfa/backup', [
            'as' => 'front.account.security.reset-backup',
            'uses' => 'Mfa\\ResetBackupController@show',
        ])->middleware('password.confirm');

        Route::post('/mfa/backup', [
            'as' => 'front.account.security.reset-backup',
            'uses' => 'Mfa\\ResetBackupController@update',
        ])->middleware('password.confirm');
    });
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('auth/minecraft/{token}', [
        'as' => 'front.auth.minecraft.token',
        'uses' => 'MinecraftPlayerLinkController@index',
    ]);
});

Route::get('bans', 'BanlistController@index')->name('front.banlist');

Route::group(['prefix' => 'panel', 'as' => 'front.panel.', 'namespace' => 'Panel', 'middleware' => ['auth', 'panel', 'requires-mfa']], function () {
    Route::view('/', 'admin.index')->name('index');

    Route::resource('accounts', 'AccountController')->only(['index', 'show', 'edit', 'update']);
    Route::resource('donations', 'DonationController');
    Route::resource('donation-perks', 'DonationPerksController')->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('minecraft-players', 'MinecraftPlayerController')->except(['destroy']);

    Route::post('minecraft-players/lookup', [
        'as' => 'minecraft-players.lookup',
        'uses' => 'MinecraftPlayerLookupController',
    ]);

    Route::post('minecraft-players/{minecraft_player}/reload-alias', [
        'as' => 'minecraft-players.reload-alias',
        'uses' => 'MinecraftPlayerReloadAliasController',
    ]);

    Route::group(['prefix' => 'api', 'as' => 'api.'], function () {
        Route::get('accounts', [
            'as' => 'account-search',
            'uses' => 'Api\\AccountSearchController',
        ]);
    });

    Route::group(['prefix' => 'accounts/{account}', 'as' => 'accounts.'], function () {
        Route::get('discourse-admin', [
            'as' => 'discourse-admin-redirect',
            'uses' => 'AccountDiscourseAdminRedirect',
        ]);

        Route::post('force-sync', [
            'as' => 'force-discourse-sync',
            'uses' => 'AccountDiscourseForceSync',
        ]);

        Route::post('activate', [
            'as' => 'activate',
            'uses' => 'AccountActivate',
        ]);

        Route::post('resend-activation', [
            'as' => 'resend-activation',
            'uses' => 'AccountResendActivation',
        ]);

        Route::post('email-change/{accountEmailChange}/approve', [
            'as' => 'email-change.approve',
            'uses' => 'AccountApproveEmailChange',
        ]);

        Route::delete('game-account/{minecraftPlayer}', [
            'as' => 'game-account.delete',
            'uses' => 'AccountGameAccount@delete',
        ]);

        Route::post('update-groups', [
            'as' => 'update-groups',
            'uses' => 'AccountUpdateGroups',
        ]);
    });
});
