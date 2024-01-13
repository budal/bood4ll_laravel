<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolesRequest;
use App\Models\Calendar;
use App\Models\Holiday;
use App\Models\User;
use Emargareten\InertiaModal\Modal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class CalendarsController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        $calendars = Calendar::filter($request, 'calendar')
            ->select('calendars.id', 'calendars.name', 'calendars.deleted_at')
            ->withCount([
                'holidays',
                'schedules',
            ])
            ->paginate(20)
            ->onEachSide(2)
            ->withQueryString();

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'calendar',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'calendar',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.calendars.create',
                                            'showIf' => Gate::allows('apps.calendars.create'),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.calendars.edit',
                                            'showIf' => Gate::allows('apps.calendars.edit')
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.calendars.destroy',
                                            'showIf' => Gate::allows('apps.calendars.destroy'),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.calendars.forcedestroy',
                                            'showIf' => Gate::allows('apps.calendars.forcedestroy') && $request->user()->can('isSuperAdmin', User::class),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.calendars.restore',
                                            'showIf' => Gate::allows('apps.calendars.restore'),
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'text',
                                            'title' => 'Name',
                                            'field' => 'name',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Holidays',
                                            'field' => 'holidays_count',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Schedules',
                                            'field' => 'schedules_count',
                                        ],
                                    ],
                                    'items' => $calendars,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function __form(Request $request, Calendar $calendar): array
    {
        $holidays = $calendar->holidays()
            ->filter($request, 'holidays')
            ->paginate(20)
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray());

        return [
            [
                'id' => 'calendar',
                'title' => 'Main data',
                'subtitle' => "Calendar's info.",
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                            'required' => true,
                            'autofocus' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'title' => 'Active',
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'holidays',
                'title' => 'Holidays',
                'subtitle' => 'Define which dates will be working days, holidays and optional points.',
                'showIf' => $calendar->id != null,
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'holidays',
                            'content' => [
                                'routes' => [
                                    'createRoute' => [
                                        'route' => 'apps.calendars.create_holiday',
                                        'attributes' => [$calendar->id],
                                        'showIf' => Gate::allows('apps.calendars.create_holiday'),
                                    ],
                                    'editRoute' => [
                                        'route' => 'apps.calendars.edit_holiday',
                                        'attributes' => [$calendar->id],
                                        'showIf' => Gate::allows('apps.calendars.edit_holiday'),
                                    ],

                                ],
                                'menu' => [
                                    [
                                        'icon' => 'mdi:plus-circle-outline',
                                        'title' => 'Authorize',
                                        'route' => [
                                            'route' => 'apps.roles.authorization',
                                            'attributes' => [
                                                $calendar->id,
                                                'on',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => false,
                                        'modalTitle' => 'Are you sure you want to authorize the selected users?|Are you sure you want to authorize the selected users?',
                                        'modalSubTitle' => 'The selected user will have the rights to access this role. Do you want to continue?|The selected user will have the rights to access this role. Do you want to continue?',
                                        'buttonTitle' => 'Authorize',
                                        'buttonIcon' => 'mdi:plus-circle-outline',
                                        'buttonColor' => 'success',
                                    ],
                                    [
                                        'icon' => 'mdi:minus-circle-outline',
                                        'title' => 'Deauthorize',
                                        'route' => [
                                            'route' => 'apps.roles.authorization',
                                            'attributes' => [
                                                $calendar->id,
                                                'off',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => true,
                                        'modalTitle' => 'Are you sure you want to deauthorize the selected users?|Are you sure you want to deauthorize the selected users?',
                                        'modalSubTitle' => 'The selected user will lose the rights to access this role. Do you want to continue?|The selected users will lose the rights to access this role. Do you want to continue?',
                                        'buttonTitle' => 'Deauthorize',
                                        'buttonIcon' => 'mdi:minus-circle-outline',
                                        'buttonColor' => 'danger',
                                    ],
                                    [
                                        'title' => '-',
                                    ],

                                    [
                                        'icon' => 'mdi:format-list-checkbox',
                                        'title' => 'List',
                                        'items' => [
                                            [
                                                'icon' => 'mdi:account-key-outline',
                                                'title' => 'Authorized users',
                                                'route' => [
                                                    'route' => 'apps.roles.edit',
                                                    'attributes' => $calendar->id,
                                                ],
                                            ],
                                            [
                                                'icon' => 'mdi:account-multiple-outline',
                                                'title' => 'All users',
                                                'route' => [
                                                    'route' => 'apps.roles.edit',
                                                    'attributes' => [$calendar->id, 'all'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'composite',
                                        'title' => 'User',
                                        'field' => 'name',
                                        'values' => [
                                            [
                                                'field' => 'name',
                                            ],
                                            [
                                                'field' => 'email',
                                                'class' => 'text-xs',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'composite',
                                        'title' => 'Classified',
                                        'class' => 'collapse',
                                        'field' => 'units_classified',
                                        'options' => [
                                            [
                                                'field' => 'name',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'composite',
                                        'title' => 'Working',
                                        'class' => 'collapse',
                                        'field' => 'units_working',
                                        'options' => [
                                            [
                                                'field' => 'name',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'toggle',
                                        'title' => '',
                                        'field' => 'checked',
                                        'disableSort' => true,
                                        'route' => [
                                            'route' => 'apps.roles.authorization',
                                            'attributes' => [
                                                $calendar->id,
                                                'toggle',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'colorOn' => 'info',
                                    ],
                                ],
                                'items' => $holidays,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function create(Request $request, Calendar $calendar): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $calendar),
            'routes' => [
                'calendar' => [
                    'route' => route('apps.calendars.store'),
                    'method' => 'post',
                ],
            ],
            'data' => [
                'active' => true,
            ],
        ]);
    }

    public function store(RolesRequest $request): RedirectResponse
    {
        $this->authorize('access', User::class);

        DB::beginTransaction();

        try {
            $calendar = new Calendar();

            $calendar->name = $request->name;
            $calendar->owner = $request->user()->id;
            $calendar->active = $request->active;
            $calendar->year = $request->year;

            $calendar->save();
        } catch (\Throwable $e) {
            report($e);

            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on add this item.|Error on add the items.',
                'toast_count' => 1,
            ]);
        }

        DB::commit();

        return Redirect::route('apps.calendars.edit', $calendar->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function edit(Request $request, Calendar $calendar): Response
    {
        $this->authorize('access', User::class);
        // $this->authorize('isActive', $calendar);
        // $this->authorize('canEdit', $calendar);
        // $this->authorize('canEditManagementRoles', $calendar);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $calendar),
            'routes' => [
                'calendar' => [
                    'route' => route('apps.calendars.edit', $calendar->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $calendar,
        ]);
    }

    public function update(Request $request, Calendar $calendar): RedirectResponse
    {
        $this->authorize('access', User::class);
        // $this->authorize('isActive', $calendar);
        // $this->authorize('isManager', User::class);
        // $this->authorize('canEdit', $calendar);
        // $this->authorize('canEditManagementRoles', $calendar);
        // $this->authorize('isOwner', $calendar);

        DB::beginTransaction();

        try {
            $calendar->name = $request->name;
            $calendar->active = $request->active;
            $calendar->year = $request->year;

            $calendar->save();
        } catch (\Exception $e) {
            report($e);

            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on edit selected item.|Error on edit selected items.',
                'toast_count' => 1,
            ]);
        }

        DB::commit();

        return Redirect::back()->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'toast_count' => 1,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('access', User::class);
        // $this->authorize('isManager', User::class);
        // $this->authorize('canDestroyOrRestore', [Calendar::class, $request]);

        try {
            $total = Calendar::whereIn('id', $request->list)->delete();

            return back()->with([
                'toast_type' => 'success',
                'toast_message' => '{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.',
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on remove selected item.|Error on remove selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function forceDestroy(Request $request): RedirectResponse
    {
        $this->authorize('access', User::class);
        // $this->authorize('isSuperAdmin', User::class);

        try {
            $total = Calendar::whereIn('id', $request->list)->forceDelete();

            return back()->with([
                'toast_type' => 'success',
                'toast_message' => '{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.',
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on erase selected item.|Error on erase selected items.',
                'toast_count' => $request->list,
            ]);
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        $this->authorize('access', User::class);
        // $this->authorize('isManager', User::class);
        // $this->authorize('canDestroyOrRestore', [Calendar::class, $request]);

        try {
            $total = Calendar::whereIn('id', $request->list)->restore();

            return back()->with([
                'toast_type' => 'success',
                'toast_message' => '{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.',
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on restore selected item.|Error on restore selected items.',
                'toast_count' => $request->list,
            ]);
        }
    }

    public function __formModal(Request $request, Holiday $holiday): array
    {
        return [
            [
                'id' => 'holiday',
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                            'required' => true,
                            'autofocus' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'title' => 'Active',
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                        [
                            'type' => 'date',
                            'name' => 'starts_at',
                            'title' => 'Starts at',
                            'required' => true,
                        ],
                        [
                            'type' => 'date',
                            'name' => 'ends_at',
                            'title' => 'Ends at',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function holidayCreate(Request $request, Holiday $holiday): Modal
    {
        // $role['abilities'] = $role->getAllAbilities()->get()->map->only('id')->pluck('id');

        return Inertia::modal('Default', [
            'form' => $this->__formModal($request, $holiday),
            'isModal' => true,
            'tabs' => false,
            'title' => 'Holiday creation',
            'routes' => [
                'holiday' => [
                    'route' => route('apps.calendars.store'),
                    'method' => 'post',
                    'buttonClass' => 'justify-end',
                ],
            ],
            'data' => [
                'active' => true,
            ],
        ])
            ->baseRoute('apps.calendars.create', $holiday)
            // ->refreshBackdrop()
        ;
    }

    public function holidayEdit(Request $request, Holiday $holiday): Modal
    {
        // $role['abilities'] = $role->getAllAbilities()->get()->map->only('id')->pluck('id');

        return Inertia::modal('Default', [
            'form' => $this->__formModal($request, $holiday),
            'isModal' => true,
            'title' => 'Define the users who have access to this authorization',
            'routes' => [
                'user' => [
                    'route' => route('apps.users.edit', $holiday->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $holiday,
        ])
            ->baseRoute('apps.users.edit', $holiday)
            // ->refreshBackdrop()
        ;
    }
}
