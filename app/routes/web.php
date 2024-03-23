<?php

use App\Http\Controllers\AbsencesController;
use App\Http\Controllers\Authorization\RolesController;
use App\Http\Controllers\Authorization\UnitsController;
use App\Http\Controllers\Authorization\UsersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HolidaysController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchedulesController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home')->breadcrumb('Home');

Route::get('/token', function () {
    return csrf_token();
});

Route::middleware('auth')->group(function () {
    Route::name('dashboard.')->group(function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('index')->breadcrumb('Dashboard')
                ->defaults('title', 'Dashboard')
                ->defaults('description', 'See all your data in one place.')
                ->defaults('icon', 'dashboard');
        });
    });

    Route::name('profile.')->group(function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile', 'edit')->name('edit')->breadcrumb('Profile')
                ->defaults('title', 'Profile')
                ->defaults('description', 'Manage your personal data.')
                ->defaults('icon', 'account_circle');
            Route::patch('/profile', 'update')->name('update');
            Route::delete('/profile', 'destroy')->name('destroy');
        });
    });

    Route::name('personal.')->group(function () {
        Route::get('/messages', [SchedulesController::class, 'show'])->name('messages.index')->breadcrumb('Messages')
            ->defaults('title', 'Messages')
            ->defaults('description', 'See all chats between you and other users.')
            ->defaults('icon', 'chat');

        Route::get('/schedule', [SchedulesController::class, 'show'])->name('schedule.index')->breadcrumb('Schedule')
            ->defaults('title', 'Schedule')
            ->defaults('description', 'Manage all your appointments.')
            ->defaults('icon', 'calendar_month');

        Route::get('/notifications', [SchedulesController::class, 'show'])->name('notifications.index')->breadcrumb('Notifications')
            ->defaults('title', 'Notifications')
            ->defaults('description', 'Manage all your appointments.')
            ->defaults('icon', 'calendar_month');
    });

    Route::name('system.')->group(function () {
        Route::get('/settings', [SchedulesController::class, 'show'])->name('settings.index')->breadcrumb('Settings')
            ->defaults('title', 'Settings')
            ->defaults('description', 'Manage all your appointments.')
            ->defaults('icon', 'settings');
    });

    Route::prefix('apps')->name('apps.')->group(function () {
        Route::controller(RolesController::class)->group(function () {
            Route::name('roles.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/roles/{cursor?}', 'index')->name('index')->breadcrumb('Roles')
                    ->defaults('title', 'Roles')
                    ->defaults('description', 'Define roles, grouping abilities to define specific access.')
                    ->defaults('icon', 'badge');

                Route::put('/roles/authorize/{role}/{mode?}', 'putAuthorize')->name('authorize');
                Route::post('/roles/create', 'postStoreRole')->name('store');
                Route::patch('/roles/edit/{role}', 'patchUpdateRole')->name('update');
                Route::delete('/roles/destroy', 'deleteDestroyRole')->name('destroy');
                Route::delete('/roles/forcedestroy', 'deleteForceDestroyRole')->name('forceDestroy');
                Route::post('/roles/restore', 'postRestoreRole')->name('restore');
            });
        });

        Route::controller(UnitsController::class)->group(function () {
            Route::name('units.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/units', 'index')->name('index')->breadcrumb('Units')
                    ->defaults('title', 'Units')
                    ->defaults('description', 'Manage units registered in the system, their subunits and users.')
                    ->defaults('icon', 'apartment');;
                Route::post('/units/create', 'store')->name('store');
                Route::patch('/units/edit/{unit}', 'update')->name('update');
                Route::post('/units/hierarchy', 'postRefreshUnitsHierarchy')->name('hierarchy');
                Route::delete('/units/destroy', 'destroy')->name('destroy');
                Route::post('/units/restore', 'restore')->name('restore');
                Route::post('/units/reorder', 'restore')->name('reorder');
            });
        });

        Route::controller(UsersController::class)->group(function () {
            Route::name('users.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/users', 'index')->name('index')->breadcrumb('Users')
                    ->defaults('title', 'Users')
                    ->defaults('description', 'Manage users informations and authorizations.')
                    ->defaults('icon', 'group');
                Route::post('/users/changeuser/{user}', 'changeUser')->name('change_user');
                Route::post('/users/returnToMyUser', 'returnToMyUser')->name('return_to_my_user');
                Route::post('/users/create', 'postUserStore')->name('store');
                Route::patch('/users/edit/{user}', 'patchUserUpdate')->name('update');
                Route::put('/users/authorize/unit/{user}/{mode?}', 'putAuthorizeUnit')->name('authorizeUnit');
                Route::put('/users/authorize/role/{user}/{mode?}', 'putAuthorizeRole')->name('authorizeRole');

                Route::delete('/users/destroy', 'deleteDestroyRole')->name('destroy');
                Route::post('/users/restore', 'postRestoreRole')->name('restore');
                Route::delete('/users/forcedestroy', 'deleteForceDestroyRole')->name('forcedestroy');
            });
        });

        Route::controller(HolidaysController::class)->group(function () {
            Route::name('holidays.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/holidays', 'index')->name('index')->breadcrumb('Holidays')
                    ->defaults('title', 'Holidays')
                    ->defaults('description', "Define which dates will be holidays and optional points.")
                    ->defaults('icon', 'beach_access');

                Route::get('/holidays/create', 'create')->name('create')->breadcrumb('Holiday creation', 'apps.holidays.index');
                Route::post('/holidays/create', 'store')->name('store');
                Route::get('/holidays/edit/{holiday}', 'edit')->name('edit')->breadcrumb('Holiday edition', 'apps.holidays.index');
                Route::patch('/holidays/edit/{holiday}', 'update')->name('update');
                Route::delete('/holidays/destroy', 'destroy')->name('destroy');
                Route::delete('/holidays/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/holidays/restore', 'restore')->name('restore');
            });
        });

        Route::controller(AbsencesController::class)->group(function () {
            Route::name('absences.')->middleware('verified')->group(function () {
                Route::get('/absences', 'index')->name('index')->breadcrumb('Absences')
                    ->defaults('title', 'Absences')
                    ->defaults('description', "Manage your staff's absences, vacations, layoffs and medical certificates.")
                    ->defaults('icon', 'no_accounts');
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
                    ->defaults('icon', 'type_specimen');
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
                    ->defaults('icon', '123');
                Route::get('/absences/vacation_plan_list/{year}', 'vacationPlanIndexByYear')->name('vacation_plan_index_list')->breadcrumb('Vacation plans', 'apps.absences.index')
                    ->defaults('title', 'Vacation plans')
                    ->defaults('description', "Manage your vacation plans.")
                    ->defaults('icon', '123');
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
                    ->defaults('icon', 'calendar_month');
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

    Route::get('/help', [ProfileController::class, 'edit'])->name('help.index')->breadcrumb('Help')
        ->defaults('title', 'Help')
        ->defaults('description', 'System manual.')
        ->defaults('icon', 'help');
});

require __DIR__ . '/auth.php';
