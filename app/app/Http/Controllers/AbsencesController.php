<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolesRequest;
use App\Models\Absence;
use App\Models\AbsencesType;
use App\Models\Calendar;
use App\Models\User;
use App\Models\VacationPlan;
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
                    'id' => 'absences',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'absences',
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
                                    'menu' => [
                                        [
                                            'icon' => 'mdi:clipboard-text-clock-outline',
                                            'title' => 'Absences types',
                                            'route' => 'apps.absences.types_index',
                                            'showIf' => $request->user()->can('isSuperAdmin', User::class),
                                        ],
                                        [
                                            'icon' => 'mdi:beach',
                                            'title' => 'Vacation plans',
                                            'route' => 'apps.absences.vacation_plan_index',
                                            'showIf' => $request->user()->can('isSuperAdmin', User::class),
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
        $absencesTypes = AbsencesType::orderBy('name')
            ->get();

        return [
            [
                'id' => 'absences',
                'title' => 'Main data',
                'subtitle' => "Absence's info.",
                'cols' => 3,
                'fields' => [
                    [
                        [
                            'type' => 'select',
                            'name' => 'parent_id',
                            'title' => 'Absence type',
                            'content' => $absencesTypes,
                            'required' => true,
                            'autofocus' => true,
                        ],
                        [
                            'type' => 'number',
                            'name' => 'year',
                            'title' => 'Year',
                            'required' => true,
                        ],
                        [
                            'type' => 'number',
                            'name' => 'duration',
                            'title' => 'Duration (days)',
                            'required' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'title' => 'Authorized',
                            'colorOn' => 'info',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'concluded',
                            'title' => 'Concluded',
                            'colorOn' => 'info',
                        ],
                    ],
                ],
            ],
            // [
            //     'id' => 'holidays',
            //     'title' => 'Holidays',
            //     'subtitle' => 'Define which dates will be working days, holidays and optional points.',
            //     'showIf' => $absence->id != null,
            //     'fields' => [
            //         [
            //             [
            //                 'type' => 'table',
            //                 'name' => 'holidays',
            //                 'content' => [
            //                     'routes' => [
            //                         'createRoute' => [
            //                             'route' => 'apps.absences.holiday_create',
            //                             'attributes' => [$absence->id],
            //                             'showIf' => Gate::allows('apps.absences.holiday_create'),
            //                         ],
            //                         'editRoute' => [
            //                             'route' => 'apps.absences.holiday_edit',
            //                             'showIf' => Gate::allows('apps.absences.holiday_edit'),
            //                         ],
            //                         'destroyRoute' => [
            //                             'route' => 'apps.absences.holiday_forcedestroy',
            //                             'showIf' => Gate::allows('apps.absences.holiday_destroy'),
            //                         ],
            //                         'forceDestroyRoute' => [
            //                             'route' => 'apps.absences.holiday_forcedestroy',
            //                             'showIf' => Gate::allows('apps.absences.holiday_forcedestroy'),
            //                         ],
            //                         'restoreRoute' => [
            //                             'route' => 'apps.absences.holiday_restore',
            //                             'showIf' => Gate::allows('apps.absences.holiday_restore'),
            //                         ],
            //                     ],
            //                     'titles' => [
            //                         [
            //                             'type' => 'text',
            //                             'title' => 'User',
            //                             'field' => 'name',
            //                         ],
            //                         [
            //                             'type' => 'text',
            //                             'title' => 'Starts at',
            //                             'field' => 'start_at',
            //                         ],
            //                         [
            //                             'type' => 'text',
            //                             'title' => 'Ends at',
            //                             'field' => 'end_at',
            //                         ],
            //                     ],
            //                     'items' => $holidays,
            //                 ],
            //             ],
            //         ],
            //     ],
            // ],
        ];
    }

    public function create(Request $request, Absence $absence): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $absence),
            'routes' => [
                'absences' => [
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
                'absences' => [
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

    public function typesIndex(Request $request): Response
    {
        $this->authorize('access', User::class);

        $absences = AbsencesType::filter($request, 'absences_types')
            ->select('absences_types.id', 'absences_types.name', 'absences_types.active', 'absences_types.max_duration', 'absences_types.working_days', 'absences_types.deleted_at')
            ->paginate(20)
            ->onEachSide(2)
            ->withQueryString();

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'absences_types',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'absences_types',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.absences.type_create',
                                            'showIf' => Gate::allows('apps.absences.type_create'),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.absences.type_edit',
                                            'showIf' => Gate::allows('apps.absences.type_edit')
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.absences.type_destroy',
                                            'showIf' => Gate::allows('apps.absences.type_destroy'),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.absences.type_forcedestroy',
                                            'showIf' => Gate::allows('apps.absences.type_forcedestroy') && $request->user()->can('isSuperAdmin', User::class),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.absences.type_restore',
                                            'showIf' => Gate::allows('apps.absences.type_restore'),
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'text',
                                            'title' => 'Name',
                                            'field' => 'name',
                                        ],
                                        [
                                            'type' => 'active',
                                            'title' => 'Active',
                                            'field' => 'active',
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

    public function __formTypes(): array
    {
        return [
            [
                'id' => 'absences_types',
                'title' => 'Main data',
                'subtitle' => 'Absence type management.',
                'cols' => 3,
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                            'span' => 2,
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
                            'type' => 'number',
                            'name' => 'max_duration',
                            'title' => 'Duration (days)',
                            'required' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'working_days',
                            'title' => 'Working days',
                            'colorOn' => 'info',
                        ],
                        [
                            'type' => 'number',
                            'name' => 'acquisition_period',
                            'title' => 'Acquisition period (days)',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function typeCreate(): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__formTypes(),
            'routes' => [
                'absences_types' => [
                    'route' => route('apps.absences.type_store'),
                    'method' => 'post',
                ],
            ],
            'data' => [
                'active' => true,
            ],
        ]);
    }

    public function typeStore(Request $request): RedirectResponse
    {
        $this->authorize('access', User::class);

        DB::beginTransaction();

        try {
            $absence_type = new AbsencesType();

            $absence_type->name = $request->name;
            $absence_type->active = $request->active;
            $absence_type->max_duration = $request->max_duration;
            $absence_type->working_days = $request->working_days;
            $absence_type->acquisition_period = $request->acquisition_period;

            $absence_type->save();
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

        return Redirect::route('apps.absences.type_edit', $absence_type->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function typeEdit(Request $request, AbsencesType $absence_type): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__formTypes(),
            'routes' => [
                'absences_types' => [
                    'route' => route('apps.absences.type_update', $absence_type->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $absence_type,
        ]);
    }

    public function typeUpdate(Request $request, AbsencesType $absence_type): RedirectResponse
    {
        $this->authorize('access', User::class);

        DB::beginTransaction();

        try {
            $absence_type->name = $request->name;
            $absence_type->active = $request->active;
            $absence_type->max_duration = $request->max_duration;
            $absence_type->working_days = $request->working_days;
            $absence_type->acquisition_period = $request->acquisition_period;

            $absence_type->save();
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

        return Redirect::route('apps.absences.type_edit', $absence_type->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'toast_count' => 1,
        ]);
    }

    public function typeDestroy(Request $request): RedirectResponse
    {
        try {
            $total = AbsencesType::whereIn('id', $request->list)->delete();

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

    public function typeForceDestroy(Request $request): RedirectResponse
    {
        try {
            $total = AbsencesType::whereIn('id', $request->list)->forceDelete();

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

    public function typeRestore(Request $request): RedirectResponse
    {
        try {
            $total = AbsencesType::whereIn('id', $request->list)->restore();

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

    public function vacationPlanIndex(Request $request): Response
    {
        $this->authorize('access', User::class);

        $vacationPlans = VacationPlan::where('vacation_plans.year', $request->vacation_plan_search ?? date('Y'))
            ->select('vacation_plans.year')
            ->selectRaw('vacation_plans.year AS id')
            ->selectRaw('COUNT(vacation_plans.year) AS periods')
            ->groupBy('vacation_plans.year')
            ->paginate(20)
            ->onEachSide(2)
            ->withQueryString();

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'vacation_plan',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'vacation_plan',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.absences.vacation_plan_create',
                                            'showIf' => Gate::allows('apps.absences.vacation_plan_create'),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.absences.vacation_plan_index_list',
                                            'showIf' => Gate::allows('apps.absences.vacation_plan_index_list')
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'text',
                                            'title' => 'Year',
                                            'field' => 'year',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Periods',
                                            'field' => 'periods',
                                        ],
                                    ],
                                    'items' => $vacationPlans,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function vacationPlanIndexByYear(Request $request): Response
    {
        $this->authorize('access', User::class);

        $absences = VacationPlan::where('year', $request->year)
            ->select('vacation_plans.id', 'vacation_plans.period', 'vacation_plans.deleted_at')
            ->selectRaw('vacation_plans.starts_at AS start_at')
            ->selectRaw('vacation_plans.ends_at AS end_at')
            ->selectRaw('vacation_plans.implantation_at AS implantation')
            ->paginate(20)
            ->onEachSide(2)
            ->withQueryString();

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'vacation_plan',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'vacation_plan',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.absences.vacation_plan_create',
                                            'showIf' => Gate::allows('apps.absences.vacation_plan_create'),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.absences.vacation_plan_edit',
                                            'showIf' => Gate::allows('apps.absences.vacation_plan_edit')
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.absences.vacation_plan_destroy',
                                            'showIf' => Gate::allows('apps.absences.vacation_plan_destroy'),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.absences.vacation_plan_forcedestroy',
                                            'showIf' => Gate::allows('apps.absences.vacation_plan_forcedestroy') && $request->user()->can('isSuperAdmin', User::class),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.absences.vacation_plan_restore',
                                            'showIf' => Gate::allows('apps.absences.vacation_plan_restore'),
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'text',
                                            'title' => 'Period',
                                            'field' => 'period',
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
                                        [
                                            'type' => 'text',
                                            'title' => 'Implantation',
                                            'field' => 'implantation',
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

    public function __formVacationPlan(): array
    {
        return [
            [
                'id' => 'vacation_plan',
                'title' => 'Main data',
                'subtitle' => 'Absence type management.',
                'cols' => 3,
                'fields' => [
                    [
                        [
                            'type' => 'number',
                            'name' => 'period',
                            'title' => 'Period',
                            'span' => 2,
                            'required' => true,
                            'autofocus' => true,
                        ],
                        [
                            'type' => 'number',
                            'name' => 'year',
                            'title' => 'Year',
                            'required' => true,
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
                        [
                            'type' => 'date',
                            'name' => 'implantation_at',
                            'title' => 'Implantation',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function vacationPlanCreate(): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__formVacationPlan(),
            'routes' => [
                'vacation_plan' => [
                    'route' => route('apps.absences.vacation_plan_store'),
                    'method' => 'post',
                ],
            ],
            'data' => [
                'active' => true,
            ],
        ]);
    }

    public function vacationPlanStore(Request $request): RedirectResponse
    {
        $this->authorize('access', User::class);

        DB::beginTransaction();

        try {
            $vacation_plan = new VacationPlan();

            $vacation_plan->period = $request->period;
            $vacation_plan->year = $request->year;
            $vacation_plan->starts_at = $request->starts_at;
            $vacation_plan->ends_at = $request->ends_at;
            $vacation_plan->implantation_at = $request->implantation_at;

            $vacation_plan->save();
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

        return Redirect::route('apps.absences.vacation_plan_index_list', $request->year)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function vacationPlanEdit(Request $request, VacationPlan $vacation_plan): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__formVacationPlan(),
            'routes' => [
                'vacation_plan' => [
                    'route' => route('apps.absences.vacation_plan_update', $vacation_plan->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $vacation_plan,
        ]);
    }

    public function vacationPlanUpdate(Request $request, VacationPlan $vacation_plan): RedirectResponse
    {
        $this->authorize('access', User::class);

        DB::beginTransaction();

        try {
            $vacation_plan->period = $request->period;
            $vacation_plan->year = $request->year;
            $vacation_plan->starts_at = $request->starts_at;
            $vacation_plan->ends_at = $request->ends_at;
            $vacation_plan->implantation_at = $request->implantation_at;

            $vacation_plan->save();
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

        return Redirect::route('apps.absences.vacation_plan_edit', $vacation_plan->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'toast_count' => 1,
        ]);
    }

    public function vacationPlanDestroy(Request $request): RedirectResponse
    {
        try {
            $total = VacationPlan::whereIn('id', $request->list)->delete();

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

    public function vacationPlanForceDestroy(Request $request): RedirectResponse
    {
        try {
            $total = VacationPlan::whereIn('id', $request->list)->forceDelete();

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

    public function vacationPlanRestore(Request $request): RedirectResponse
    {
        try {
            $total = VacationPlan::whereIn('id', $request->list)->restore();

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
