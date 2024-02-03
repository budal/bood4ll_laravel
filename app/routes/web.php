<?php

use App\Http\Controllers\AbsencesController;
use App\Http\Controllers\Authorization\RolesController;
use App\Http\Controllers\Authorization\UnitsController;
use App\Http\Controllers\Authorization\UsersController;
use App\Http\Controllers\CalendarsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchedulesController;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home')->breadcrumb('Home');

Route::get('/auth/{provider}/redirect', function (string $provider) {
    return Socialite::driver($provider)->redirect();
})->name('authRedirect');

Route::get('/auth/{provider}/callback', function (string $provider) {
    $providerUser = Socialite::driver($provider)->user();

    $user = User::updateOrCreate([
        'email' => $providerUser->getEmail(),
        'active' => true,
    ], [
        'name' => $providerUser->getName(),
        'provider_name' => $provider,
        'provider_id' => $providerUser->getId(),
        'provider_avatar' => $providerUser->getAvatar(),
        'provider_token' => $providerUser->token,
        'provider_refresh_token' => $providerUser->refreshToken,
    ]);

    dd($user);

    // event(new Registered($user));

    // Auth::login($user);

    // return redirect('/dashboard');
})->name('authCallback');

Route::middleware('auth')->group(function () {
    Route::name('dashboard.')->group(function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('index')->breadcrumb('Dashboard')
                ->defaults('title', 'Dashboard')
                ->defaults('description', 'See all your related data in one place.')
                ->defaults('icon', 'mdi:monitor-dashboard');
            // Route::delete('/profile', 'destroy')->name('destroy');
        });
    });

    Route::name('profile.')->group(function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile', 'edit')->name('edit')->breadcrumb('Profile')
                ->defaults('title', 'Profile')
                ->defaults('description', 'Manage your personal data.')
                ->defaults('icon', 'mdi:account-details');
            Route::patch('/profile', 'update')->name('update');
            Route::delete('/profile', 'destroy')->name('destroy');
        });
    });

    Route::get('/messages', [ProfileController::class, 'edit'])->name('messages')->breadcrumb('Messages')
        ->defaults('title', 'Messages')
        ->defaults('description', 'See all chats between you and other users.')
        ->defaults('icon', 'mdi:chat-outline');

    Route::get('/schedule', [ProfileController::class, 'edit'])->name('schedule')->breadcrumb('Schedule')
        ->defaults('title', 'Schedule')
        ->defaults('description', 'Manage all your appointments.')
        ->defaults('icon', 'mdi:calendar-multiselect-outline');

    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings')->breadcrumb('Settings')
        ->defaults('title', 'Settings')
        ->defaults('description', 'Personalize how the system is showed to you.')
        ->defaults('icon', 'mdi:cog-outline');

    Route::prefix('apps')->name('apps.')->group(function () {
        Route::controller(RolesController::class)->group(function () {
            Route::name('roles.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/roles', 'index')->name('index')->breadcrumb('Roles')
                    ->defaults('title', 'Roles')
                    ->defaults('description', 'Define roles, grouping abilities to define specific access.')
                    ->defaults('icon', 'mdi:account-details-outline');
                Route::get('/roles/create', 'create')->name('create')->breadcrumb('Role creation', 'apps.roles.index');
                Route::post('/roles/create', 'store')->name('store');
                Route::get('/roles/edit/{role}/{show?}', 'edit')->name('edit')->breadcrumb('Role edition', 'apps.roles.index');
                Route::patch('/roles/edit/{role}', 'update')->name('update');
                Route::post('/roles/authorization/{role}/{mode?}', 'authorization')->name('authorization');
                Route::delete('/roles/destroy', 'destroy')->name('destroy');
                Route::delete('/roles/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/roles/restore', 'restore')->name('restore');

                Route::get('/roles/abilities', 'abilitiesIndex')->name('abilities_index')->breadcrumb('Abilities', 'apps.roles.index')
                    ->defaults('title', 'Abilities')
                    ->defaults('description', 'Define which abilities will be showed in the roles management.')
                    ->defaults('icon', 'mdi:book-cog-outline');
                Route::post('/roles/abilities/update/{mode?}', 'abilitiesUpdate')->name('abilities_update')->whereIn('mode', ['toggle', 'on', 'off']);
            });
        });

        Route::controller(UnitsController::class)->group(function () {
            Route::name('units.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/units', 'index')->name('index')->breadcrumb('Units')
                    ->defaults('title', 'Units')
                    ->defaults('description', 'Manage units registered in the system, their subunits and users.')
                    ->defaults('icon', 'mdi:home-group');;
                Route::get('/units/create/{unit?}', 'create')->name('create')->breadcrumb('Unit creation', 'apps.units.index');
                Route::post('/units/create', 'store')->name('store');
                Route::get('/units/edit/{unit}/{show?}', 'edit')->name('edit')->breadcrumb('Unit edition', 'apps.units.index');
                Route::patch('/units/edit/{unit}', 'update')->name('update');
                Route::post('/units/hierarchy', 'hierarchy')->name('hierarchy');
                Route::delete('/units/destroy', 'destroy')->name('destroy');
                Route::post('/units/restore/{unit}', 'restore')->name('restore');
            });
        });

        Route::controller(UsersController::class)->group(function () {
            Route::name('users.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/users', 'index')->name('index')->breadcrumb('Users')
                    ->defaults('title', 'Users')
                    ->defaults('description', 'Manage users informations and authorizations.')
                    ->defaults('icon', 'mdi:account-multiple');
                Route::post('/users/changeuser/{user}', 'changeUser')->name('change_user');
                Route::post('/users/returnToMyUser', 'returnToMyUser')->name('return_to_my_user');
                Route::post('/users/activate/{user}/{mode?}', 'activate')->name('activate');
                Route::get('/users/create', 'create')->name('create')->breadcrumb('User creation', 'apps.users.index');
                Route::post('/users/create', 'store')->name('store');
                Route::get('/users/edit/{user}/{show?}', 'edit')->name('edit')->breadcrumb('User edition', 'apps.users.index');
                Route::patch('/users/edit/{user}', 'update')->name('update');
                Route::post('/users/authorize_unit/{user}/{mode?}', 'authorizeUnit')->name('authorize_unit');
                Route::post('/users/authorize_role/{user}/{mode?}', 'authorizeRole')->name('authorize_role');
                Route::delete('/users/destroy', 'destroy')->name('destroy');
                Route::delete('/users/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/users/restore', 'restore')->name('restore');
            });
        });

        Route::controller(CalendarsController::class)->group(function () {
            Route::name('calendars.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/calendars', 'index')->name('index')->breadcrumb('Calendars')
                    ->defaults('title', 'Calendars')
                    ->defaults('description', "Manage calendars with working days, holidays and optional points.")
                    ->defaults('icon', 'mdi:calendars-multiselect-outline');
                Route::get('/calendars/create', 'create')->name('create')->breadcrumb('Calendar creation', 'apps.calendars.index');
                Route::post('/calendars/create', 'store')->name('store');
                Route::get('/calendars/edit/{calendar}', 'edit')->name('edit')->breadcrumb('Calendar edition', 'apps.calendars.index');
                Route::patch('/calendars/edit/{calendar}', 'update')->name('update');
                Route::delete('/calendars/destroy', 'destroy')->name('destroy');
                Route::delete('/calendars/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/calendars/restore', 'restore')->name('restore');

                Route::get('/calendars/holiday/create/{calendar}', 'holidayCreate')->name('holiday_create')->breadcrumb('Holiday creation', 'apps.calendars.index');
                Route::post('/calendars/holiday/create/{calendar}', 'holidayStore')->name('holiday_store');
                Route::get('/calendars/holiday/edit/{holiday}', 'holidayEdit')->name('holiday_edit')->breadcrumb('Holiday edition', 'apps.calendars.index');
                Route::patch('/calendars/holiday/edit/{holiday}', 'holidayUpdate')->name('holiday_update');
                Route::delete('/calendars/holiday/destroy', 'holidayDestroy')->name('holiday_destroy');
                Route::delete('/calendars/holiday/forcedestroy', 'holidayForceDestroy')->name('holiday_forcedestroy');
                Route::post('/calendars/holiday/restore', 'holidayRestore')->name('holiday_restore');
            });
        });

        Route::controller(AbsencesController::class)->group(function () {
            Route::name('absences.')->middleware('verified')->group(function () {
                Route::get('/absences', 'index')->name('index')->breadcrumb('Absences')
                    ->defaults('title', 'Absences')
                    ->defaults('description', "Manage your staff's absences, vacations, layoffs and medical certificates.")
                    ->defaults('icon', 'mdi:calendar-multiselect-outline');
                Route::get('/absences/create', 'create')->name('create')->breadcrumb('Absence creation', 'apps.absences.index');
                Route::post('/absences/create', 'store')->name('store');
                Route::get('/absences/edit/{role}/{show?}', 'edit')->name('edit')->breadcrumb('Absence edition', 'apps.absences.index');
                Route::patch('/absences/edit/{role}', 'update')->name('update');
                Route::post('/absences/authorization/{role}/{mode?}', 'authorization')->name('authorization');
                Route::delete('/absences/destroy', 'destroy')->name('destroy');
                Route::delete('/absences/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/absences/restore', 'restore')->name('restore');

                Route::get('/absences/types', 'typesIndex')->name('types_index')->breadcrumb('Absences types', 'apps.absences.index')
                    ->defaults('title', 'Absences types')
                    ->defaults('description', "Manage absences types.")
                    ->defaults('icon', 'mdi:clipboard-text-clock-outline');
                Route::get('/absences/types/create', 'typeCreate')->name('type_create')->breadcrumb('Absence type creation', 'apps.absences.types_index');
                Route::post('/absences/types/create', 'typeStore')->name('type_store');
                Route::get('/absences/types/edit/{absence_type}', 'typeEdit')->name('type_edit')->breadcrumb('Absence type edition', 'apps.absences.types_index');
                Route::patch('/absences/types/edit/{absence_type}', 'typeUpdate')->name('type_update');
                Route::delete('/absences/types/destroy', 'typeDestroy')->name('type_destroy');
                Route::delete('/absences/types/forcedestroy', 'typeForceDestroy')->name('type_forcedestroy');
                Route::post('/absences/types/restore', 'typeRestore')->name('type_restore');

                Route::get('/absences/vacation_plan', 'vacationPlanIndex')->name('vacation_plan_index')->breadcrumb('Vacation plans', 'apps.absences.index')
                    ->defaults('title', 'Vacation plans')
                    ->defaults('description', "Manage your vacation plans.")
                    ->defaults('icon', 'mdi:beach');
                Route::get('/absences/vacation_plan_list/{year}', 'vacationPlanIndexByYear')->name('vacation_plan_index_list')->breadcrumb('Vacation plans', 'apps.absences.index')
                    ->defaults('title', 'Vacation plans')
                    ->defaults('description', "Manage your vacation plans.")
                    ->defaults('icon', 'mdi:beach');
                Route::get('/absences/vacation_plan/create', 'vacationPlanCreate')->name('vacation_plan_create')->breadcrumb('Vacation plan creation', 'apps.absences.vacation_plan_index');
                Route::post('/absences/vacation_plan/create', 'vacationPlanStore')->name('vacation_plan_store');
                Route::get('/absences/vacation_plan/edit/{vacation_plan}', 'vacationPlanEdit')->name('vacation_plan_edit')->breadcrumb('Vacation plan edition', 'apps.absences.vacation_plan_index');
                Route::patch('/absences/vacation_plan/edit/{vacation_plan}', 'vacationPlanUpdate')->name('vacation_plan_update');
                Route::delete('/absences/vacation_plan/destroy', 'vacationPlanDestroy')->name('vacation_plan_destroy');
                Route::delete('/absences/vacation_plan/forcedestroy', 'vacationPlanForceDestroy')->name('vacation_plan_forcedestroy');
                Route::post('/absences/vacation_plan/restore', 'vacationPlanRestore')->name('vacation_plan_restore');
            });
        });

        Route::controller(SchedulesController::class)->group(function () {
            Route::name('schedules.')->middleware('verified')->group(function () {
                Route::get('/schedules', 'index')->name('index')->breadcrumb('Schedules')
                    ->defaults('title', 'Schedules')
                    ->defaults('description', "Manage your team's commitments, schedules and events.")
                    ->defaults('icon', 'mdi:calendar-multiselect-outline');
                Route::get('/schedules/create', 'create')->name('create')->breadcrumb('Schedule creation', 'apps.schedules.index');
                Route::post('/schedules/create', 'store')->name('store');
                Route::get('/schedules/edit/{role}/{show?}', 'edit')->name('edit')->breadcrumb('Schedule edition', 'apps.schedules.index');
                Route::patch('/schedules/edit/{role}', 'update')->name('update');
                Route::post('/schedules/authorization/{role}/{mode?}', 'authorization')->name('authorization');
                Route::delete('/schedules/destroy', 'destroy')->name('destroy');
                Route::delete('/schedules/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/schedules/restore', 'restore')->name('restore');
            });
        });
    });

    Route::get('/reports', [ProfileController::class, 'edit'])->name('reports')->breadcrumb('Reports')
        ->defaults('title', 'Reports')
        ->defaults('description', 'See all data registered in the system.')
        ->defaults('icon', 'mdi:chart-areaspline');

    Route::get('/help', [ProfileController::class, 'edit'])->name('help')->breadcrumb('Help')
        ->defaults('title', 'Help')
        ->defaults('description', 'System manual.')
        ->defaults('icon', 'mdi:help-circle-outline');
});

require __DIR__ . '/auth.php';
