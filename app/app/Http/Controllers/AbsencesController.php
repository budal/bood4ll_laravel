<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolesRequest;
use App\Models\Absence;
use App\Models\AbsencesType;
use App\Models\Calendar;
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

class AbsencesController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        $absences = Absence::filter($request, 'absence')
            ->select('absence.id', 'absence.name', 'absence.year', 'absence.deleted_at')
            ->withCount('users')
            ->paginate(20)
            ->onEachSide(2)
            ->withQueryString();

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'absence',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'absence',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.absences.create',
                                            'showIf' => Gate::allows('apps.absences.create'),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.absences.edit',
                                            'showIf' => Gate::allows('apps.absences.edit')
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.absences.destroy',
                                            'showIf' => Gate::allows('apps.absences.destroy'),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.absences.forcedestroy',
                                            'showIf' => Gate::allows('apps.absences.forcedestroy') && $request->user()->can('isSuperAdmin', User::class),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.absences.restore',
                                            'showIf' => Gate::allows('apps.absences.restore'),
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
                                            'title' => 'Year',
                                            'field' => 'year',
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
                                    'items' => $absences,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function __form(Request $request, Absence $absence): array
    {
        $holidays = $absence->holidays()
            ->filter($request, 'holidays', ['order' => ['start_at']])
            ->paginate(20)
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray());

        // dd($holidays);

        return [
            [
                'id' => 'absence',
                'title' => 'Main data',
                'subtitle' => "Absence's info.",
                'cols' => 3,
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
                            'type' => 'input',
                            'name' => 'year',
                            'title' => 'Year',
                            'required' => true,
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
                'showIf' => $absence->id != null,
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'holidays',
                            'content' => [
                                'routes' => [
                                    'createRoute' => [
                                        'route' => 'apps.absences.holiday_create',
                                        'attributes' => [$absence->id],
                                        'showIf' => Gate::allows('apps.absences.holiday_create'),
                                    ],
                                    'editRoute' => [
                                        'route' => 'apps.absences.holiday_edit',
                                        'showIf' => Gate::allows('apps.absences.holiday_edit'),
                                    ],
                                    'destroyRoute' => [
                                        'route' => 'apps.absences.holiday_forcedestroy',
                                        'showIf' => Gate::allows('apps.absences.holiday_destroy'),
                                    ],
                                    'forceDestroyRoute' => [
                                        'route' => 'apps.absences.holiday_forcedestroy',
                                        'showIf' => Gate::allows('apps.absences.holiday_forcedestroy'),
                                    ],
                                    'restoreRoute' => [
                                        'route' => 'apps.absences.holiday_restore',
                                        'showIf' => Gate::allows('apps.absences.holiday_restore'),
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'text',
                                        'title' => 'User',
                                        'field' => 'name',
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Starts at',
                                        'field' => 'start_at',
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Ends at',
                                        'field' => 'end_at',
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

    public function create(Request $request, Absence $absence): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $absence),
            'routes' => [
                'absence' => [
                    'route' => route('apps.absences.store'),
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
            $absence = new Absence();

            $absence->name = $request->name;
            $absence->owner = $request->user()->id;
            $absence->active = $request->active;

            $absence->save();
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

        return Redirect::route('apps.absences.edit', $absence->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function edit(Request $request, Absence $absence): Response
    {
        $this->authorize('access', User::class);
        // $this->authorize('isActive', $absence);
        // $this->authorize('canEdit', $absence);
        // $this->authorize('canEditManagementRoles', $absence);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $absence),
            'routes' => [
                'absence' => [
                    'route' => route('apps.absences.edit', $absence->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $absence,
        ]);
    }

    public function update(Request $request, Absence $absence): RedirectResponse
    {
        $this->authorize('access', User::class);
        // $this->authorize('isActive', $absence);
        // $this->authorize('isManager', User::class);
        // $this->authorize('canEdit', $absence);
        // $this->authorize('canEditManagementRoles', $absence);
        // $this->authorize('isOwner', $absence);

        DB::beginTransaction();

        try {
            $absence->name = $request->name;
            $absence->active = $request->active;
            $absence->year = $request->year;

            $absence->save();
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
        // $this->authorize('canDestroyOrRestore', [Absence::class, $request]);

        try {
            $total = Absence::whereIn('id', $request->list)->delete();

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
            $total = Absence::whereIn('id', $request->list)->forceDelete();

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
        // $this->authorize('canDestroyOrRestore', [Absence::class, $request]);

        try {
            $total = Absence::whereIn('id', $request->list)->restore();

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

    public function __formModal(): array
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
                            'type' => 'datetime-local',
                            'name' => 'starts_at',
                            'title' => 'Starts at',
                            'required' => true,
                        ],
                        [
                            'type' => 'datetime-local',
                            'name' => 'ends_at',
                            'title' => 'Ends at',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function holidayCreate(Request $request, Absence $absence): Modal
    {
        return Inertia::modal('Default', [
            'form' => $this->__formModal(),
            'isModal' => true,
            'tabs' => false,
            'title' => 'Holiday creation',
            'routes' => [
                'holiday' => [
                    'route' => route('apps.absences.holiday_store', $absence->id),
                    'method' => 'post',
                    'buttonClass' => 'justify-end',
                ],
            ],
            'data' => [
                'active' => true,
            ],
        ])
            ->baseRoute('apps.absences.edit', $absence->id)
            ->refreshBackdrop();
    }

    public function holidayStore(Request $request, Absence $absence): RedirectResponse
    {
        $this->authorize('access', User::class);

        DB::beginTransaction();

        try {
            $holiday = new Holiday();

            $holiday->name = $request->name;
            $holiday->active = $request->active;
            $holiday->starts_at = $request->starts_at;
            $holiday->ends_at = $request->ends_at;

            $holiday->save();

            try {
                $absence->holidays()->attach($holiday->id);
            } catch (\Exception $e) {
                report($e);

                DB::rollback();

                return Redirect::back()->with([
                    'toast_type' => 'error',
                    'toast_message' => 'Error when syncing holidays in the absence.',
                ]);
            }
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

        return Redirect::route('apps.absences.edit', $absence->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function holidayEdit(Request $request, Holiday $holiday): Modal
    {
        return Inertia::modal('Default', [
            'form' => $this->__formModal(),
            'isModal' => true,
            'tabs' => false,
            'title' => 'Holiday creation',
            'routes' => [
                'holiday' => [
                    'route' => route('apps.absences.holiday_update', $holiday->id),
                    'method' => 'patch',
                    'buttonClass' => 'justify-end',
                ],
            ],
            'data' => $holiday,
        ])
            ->baseRoute('apps.absences.edit', $holiday->absences()->first()->id)
            ->refreshBackdrop();
    }

    public function holidayUpdate(Request $request, Absence $absence, Holiday $holiday): RedirectResponse
    {
        $this->authorize('access', User::class);

        DB::beginTransaction();

        try {
            $holiday->name = $request->name;
            $holiday->active = $request->active;
            $holiday->starts_at = $request->starts_at;
            $holiday->ends_at = $request->ends_at;

            $holiday->save();
        } catch (\Throwable $e) {
            report($e);

            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on edit selected item.|Error on edit selected items.',
                'toast_count' => 1,
            ]);
        }

        DB::commit();

        return Redirect::route('apps.absences.edit', $holiday->absences()->first()->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'toast_count' => 1,
        ]);
    }

    public function holidayDestroy(Request $request): RedirectResponse
    {
        try {
            $total = Holiday::whereIn('id', $request->list)->delete();

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

    public function holidayForceDestroy(Request $request): RedirectResponse
    {
        try {
            $total = Holiday::whereIn('id', $request->list)->forceDelete();

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
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function holidayRestore(Request $request): RedirectResponse
    {
        try {
            $total = Holiday::whereIn('id', $request->list)->restore();

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
                'toast_count' => count($request->list),
            ]);
        }
    }
}
