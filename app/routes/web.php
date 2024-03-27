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
})->name('home');

Route::get('/token', function () {
    return csrf_token();
});

Route::middleware('auth')->group(function () {
    Route::name('dashboard.')->group(function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('index')
                ->defaults('title', 'Dashboard')
                ->defaults('description', 'See all your data in one place.')
                ->defaults('icon', 'dashboard');
        });
    });

    Route::name('profile.')->group(function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile', 'edit')->name('edit')
                ->defaults('title', 'Profile')
                ->defaults('description', 'Manage your personal data.')
                ->defaults('icon', 'account_circle');
            Route::patch('/profile', 'update')->name('update');
            Route::delete('/profile', 'destroy')->name('destroy');
        });
    });

    Route::name('personal.')->group(function () {
        Route::get('/messages', [SchedulesController::class, 'show'])->name('messages.index')
            ->defaults('title', 'Messages')
            ->defaults('description', 'See all chats between you and other users.')
            ->defaults('icon', 'chat');

        Route::get('/schedule', [SchedulesController::class, 'show'])->name('schedule.index')
            ->defaults('title', 'Schedule')
            ->defaults('description', 'Manage all your appointments.')
            ->defaults('icon', 'calendar_month');

        Route::get('/notifications', [SchedulesController::class, 'show'])->name('notifications.index')
            ->defaults('title', 'Notifications')
            ->defaults('description', 'Manage all your appointments.')
            ->defaults('icon', 'calendar_month');
    });

    Route::name('system.')->group(function () {
        Route::get('/settings', [SchedulesController::class, 'show'])->name('settings.index')
            ->defaults('title', 'Settings')
            ->defaults('description', 'Manage all your appointments.')
            ->defaults('icon', 'settings');
    });

    Route::prefix('apps')->name('apps.')->group(function () {
        Route::controller(RolesController::class)->group(function () {
            Route::name('roles.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/roles/{cursor?}', 'index')->name('index')
                    ->defaults('title', 'Roles')
                    ->defaults('description', 'Define roles, grouping abilities to define specific access.')
                    ->defaults('icon', 'badge');

                Route::put('/roles/authorize/{role}/{mode?}', 'putRoleAuthorize')->name('authorize');
                Route::post('/roles/create', 'postRoleStore')->name('store');
                Route::patch('/roles/edit/{role}', 'patchRoleUpdate')->name('update');
                Route::delete('/roles/destroy', 'deleteRoleDestroy')->name('destroy');
                Route::delete('/roles/forcedestroy', 'deleteRoleForceDestroy')->name('forceDestroy');
                Route::post('/roles/restore', 'postRoleRestore')->name('restore');
            });
        });

        Route::controller(UnitsController::class)->group(function () {
            Route::name('units.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/units', 'index')->name('index')
                    ->defaults('title', 'Units')
                    ->defaults('description', 'Manage units registered in the system, their subunits and users.')
                    ->defaults('icon', 'apartment');
                Route::post('/units/hierarchy', 'postRefreshUnitsHierarchy')->name('hierarchy');
                Route::post('/units/create', 'postUnitStore')->name('store');
                Route::patch('/units/edit/{unit}', 'patchUnitUpdate')->name('update');
                Route::delete('/units/destroy', 'deleteUnitsDestroy')->name('destroy');
                Route::post('/units/restore', 'postUnitsRestore')->name('restore');
                Route::delete('/units/destroy', 'deleteUnitsForceDestroy')->name('forceDestroy');
                Route::post('/units/reorder', 'postUnitsReorder')->name('reorder');
            });
        });

        Route::controller(UsersController::class)->group(function () {
            Route::name('users.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/users', 'index')->name('index')
                    ->defaults('title', 'Users')
                    ->defaults('description', 'Manage users informations and authorizations.')
                    ->defaults('icon', 'group');
                Route::post('/users/changeuser/{user}', 'changeUser')->name('change_user');
                Route::post('/users/returnToMyUser', 'returnToMyUser')->name('return_to_my_user');
                Route::post('/users/create', 'postUserStore')->name('store');
                Route::patch('/users/edit/{user}', 'patchUserUpdate')->name('update');
                Route::put('/users/authorize/unit/{user}/{mode?}', 'putAuthorizeUnit')->name('authorizeUnit');
                Route::put('/users/authorize/role/{user}/{mode?}', 'putAuthorizeRole')->name('authorizeRole');

                Route::delete('/users/destroy', 'deleteUsersDestroy')->name('destroy');
                Route::post('/users/restore', 'postUsersRestore')->name('restore');
                Route::delete('/users/forcedestroy', 'deleteUsersForceDestroy')->name('forceDestroy');
            });
        });

        Route::controller(HolidaysController::class)->group(function () {
            Route::name('holidays.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/holidays', 'index')->name('index')
                    ->defaults('title', 'Holidays')
                    ->defaults('description', "Define which dates will be holidays and optional points.")
                    ->defaults('icon', 'beach_access');

                Route::post('/holidays/create', 'store')->name('store');
                Route::patch('/holidays/edit/{holiday}', 'update')->name('update');
                Route::delete('/holidays/destroy', 'destroy')->name('destroy');
                Route::post('/holidays/restore', 'restore')->name('restore');
                Route::delete('/holidays/forcedestroy', 'forceDestroy')->name('forceDestroy');
            });
        });

        Route::controller(AbsencesController::class)->group(function () {
            Route::name('absences.')->middleware('verified')->group(function () {
                Route::get('/absences', 'index')->name('index')
                    ->defaults('title', 'Absences')
                    ->defaults('description', "Manage your staff's absences, vacations, layoffs and medical certificates.")
                    ->defaults('icon', 'no_accounts');
                Route::get('/absences/create', 'create')->name('create');
                Route::post('/absences/create', 'store')->name('store');
                Route::get('/absences/edit/{role}/{show?}', 'edit')->name('edit');
                Route::patch('/absences/edit/{role}', 'update')->name('update');
                Route::post('/absences/authorization/{role}/{mode?}', 'authorization')->name('authorization');
                Route::delete('/absences/destroy', 'destroy')->name('destroy');
                Route::delete('/absences/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/absences/restore', 'restore')->name('restore');

                Route::get('/absences/types', 'typesIndex')->name('types_index')
                    ->defaults('title', 'Absences types')
                    ->defaults('description', "Manage absences types.")
                    ->defaults('icon', 'type_specimen');
                Route::get('/absences/types/create', 'typeCreate')->name('type_create');
                Route::post('/absences/types/create', 'typeStore')->name('type_store');
                Route::get('/absences/types/edit/{absence_type}', 'typeEdit')->name('type_edit');
                Route::patch('/absences/types/edit/{absence_type}', 'typeUpdate')->name('type_update');
                Route::delete('/absences/types/destroy', 'typeDestroy')->name('type_destroy');
                Route::delete('/absences/types/forcedestroy', 'typeForceDestroy')->name('type_forcedestroy');
                Route::post('/absences/types/restore', 'typeRestore')->name('type_restore');

                Route::get('/absences/vacation_plan', 'vacationPlanIndex')->name('vacation_plan_index')
                    ->defaults('title', 'Vacation plans')
                    ->defaults('description', "Manage your vacation plans.")
                    ->defaults('icon', '123');
                Route::get('/absences/vacation_plan_list/{year}', 'vacationPlanIndexByYear')->name('vacation_plan_index_list')
                    ->defaults('title', 'Vacation plans')
                    ->defaults('description', "Manage your vacation plans.")
                    ->defaults('icon', '123');
                Route::get('/absences/vacation_plan/create', 'vacationPlanCreate')->name('vacation_plan_create');
                Route::post('/absences/vacation_plan/create', 'vacationPlanStore')->name('vacation_plan_store');
                Route::get('/absences/vacation_plan/edit/{vacation_plan}', 'vacationPlanEdit')->name('vacation_plan_edit');
                Route::patch('/absences/vacation_plan/edit/{vacation_plan}', 'vacationPlanUpdate')->name('vacation_plan_update');
                Route::delete('/absences/vacation_plan/destroy', 'vacationPlanDestroy')->name('vacation_plan_destroy');
                Route::delete('/absences/vacation_plan/forcedestroy', 'vacationPlanForceDestroy')->name('vacation_plan_forcedestroy');
                Route::post('/absences/vacation_plan/restore', 'vacationPlanRestore')->name('vacation_plan_restore');
            });
        });

        Route::controller(SchedulesController::class)->group(function () {
            Route::name('schedules.')->middleware('verified')->group(function () {
                Route::get('/schedules', 'index')->name('index')
                    ->defaults('title', 'Schedules')
                    ->defaults('description', "Manage your team's commitments, schedules and events.")
                    ->defaults('icon', 'calendar_month');
                Route::get('/schedules/create', 'create')->name('create');
                Route::post('/schedules/create', 'store')->name('store');
                Route::get('/schedules/edit/{role}/{show?}', 'edit')->name('edit');
                Route::patch('/schedules/edit/{role}', 'update')->name('update');
                Route::post('/schedules/authorization/{role}/{mode?}', 'authorization')->name('authorization');
                Route::delete('/schedules/destroy', 'destroy')->name('destroy');
                Route::delete('/schedules/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/schedules/restore', 'restore')->name('restore');
            });
        });
    });

    Route::get('/help', [ProfileController::class, 'edit'])->name('help.index')
        ->defaults('title', 'Help')
        ->defaults('description', 'System manual.')
        ->defaults('icon', 'help');
});

require __DIR__ . '/auth.php';
