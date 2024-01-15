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
        // $holidays = $absence->holidays()
        //     ->filter($request, 'holidays', ['order' => ['start_at']])
        //     ->paginate(20)
        //     ->onEachSide(2)
        //     ->appends(collect($request->query)->toArray());

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

    public function typesIndex(Request $request): Response
    {
        $this->authorize('access', User::class);

        $absences = AbsencesType::filter($request, 'absences_types')
            ->select('absences_types.id', 'absences_types.name', 'absences_types.active', 'absences_types.duration', 'absences_types.working_days', 'absences_types.deleted_at')
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
                                            'type' => 'text',
                                            'title' => 'Active',
                                            'field' => 'active',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Duration',
                                            'field' => 'duration',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Working days',
                                            'field' => 'working_days',
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
                            'type' => 'number',
                            'name' => 'duration',
                            'title' => 'Duration',
                            'required' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'working_days',
                            'title' => 'Working days',
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
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
            $absences_types = new AbsencesType();

            $absences_types->name = $request->name;
            $absences_types->active = $request->active;
            $absences_types->duration = $request->duration;
            $absences_types->working_days = $request->working_days;

            $absences_types->save();
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

        return Redirect::route('apps.absences.type_edit', $absences_types->id)->with([
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

    public function typeUpdate(Request $request, Absence $absence, AbsencesType $absence_type): RedirectResponse
    {
        $this->authorize('access', User::class);

        DB::beginTransaction();

        try {
            $absence_type->name = $request->name;
            $absence_type->active = $request->active;
            $absence_type->duration = $request->duration;
            $absence_type->working_days = $request->working_days;

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
}
