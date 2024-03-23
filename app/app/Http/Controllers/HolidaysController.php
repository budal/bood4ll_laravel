<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use App\Models\Holiday;
use App\Models\User;
use Emargareten\InertiaModal\Modal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HolidaysController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        $holidays = Holiday::filter($request, 'holiday', ['order' => ['start_at']])
            ->select('holidays.*')
            ->getDates()
            ->paginate(20)
            ->onEachSide(2)
            ->withQueryString();

        return Inertia::render('Default', [
            'tabs' => false,
            'form' => [
                [
                    'id' => 'holiday',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'holiday',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.holidays.create',
                                            'showIf' => Gate::allows('apps.holidays.create'),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.holidays.edit',
                                            'showIf' => Gate::allows('apps.holidays.edit')
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.holidays.destroy',
                                            'showIf' => Gate::allows('apps.holidays.destroy'),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.holidays.forcedestroy',
                                            'showIf' => Gate::allows('apps.holidays.forcedestroy') && $request->user()->can('isSuperAdmin', User::class),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.holidays.restore',
                                            'showIf' => Gate::allows('apps.holidays.restore'),
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'composite',
                                            'title' => 'Name',
                                            'field' => 'name',
                                            'values' => [
                                                [
                                                    'field' => 'name',
                                                ],
                                                [
                                                    'field' => 'authority',
                                                    'class' => 'text-xs',
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'active',
                                            'title' => 'Day off',
                                            'field' => 'day_off',
                                        ],
                                        [
                                            'type' => 'composite',
                                            'title' => 'Duration',
                                            'field' => 'start_at',
                                            'values' => [
                                                [
                                                    'field' => 'start_at',
                                                    'class' => 'text-xs',
                                                ],
                                                [
                                                    'field' => 'end_at',
                                                    'class' => 'text-xs',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'items' => $holidays,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function __form(): array
    {
        return [
            [
                'id' => 'holiday',
                'cols' => 8,
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                            'span' => 6,
                            'required' => true,
                            'autofocus' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'title' => 'Active',
                            'span' => 2,
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'authority',
                            'title' => 'Authority',
                            'span' => 4,
                            'required' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'day_off',
                            'title' => 'Day off',
                            'span' => 2,
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'easter',
                            'title' => 'Easter',
                            'span' => 2,
                            'colorOn' => 'info',
                        ],
                        [
                            'type' => 'date',
                            'name' => 'date',
                            'title' => 'Date',
                            'span' => 4,
                            'required' => true,
                        ],
                        [
                            'type' => 'time',
                            'name' => 'start_time',
                            'title' => 'Start at',
                            'span' => 2,
                            'required' => true,
                        ],
                        [
                            'type' => 'time',
                            'name' => 'end_time',
                            'title' => 'End at',
                            'span' => 2,
                            'required' => true,
                        ],
                        [
                            'type' => 'text',
                            'name' => 'operator',
                            'title' => 'Operator',
                            'span' => 2,
                            'required' => true,
                        ],
                        [
                            'type' => 'text',
                            'name' => 'difference_start',
                            'title' => 'Start of difference',
                            'span' => 3,
                            'required' => true,
                        ],
                        [
                            'type' => 'text',
                            'name' => 'difference_end',
                            'title' => 'End of difference',
                            'span' => 3,
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function create(Request $request): Modal
    {
        return Inertia::modal('Default', [
            'form' => $this->__form(),
            'isModal' => true,
            'title' => 'Holiday creation',
            'routes' => [
                'holiday' => [
                    'route' => route('apps.holidays.store'),
                    'method' => 'post',
                    'buttonClass' => 'justify-end',
                ],
            ],
            'data' => [
                'active' => true,
            ],
        ])
            ->baseRoute('apps.holidays.index')
            // ->refreshBackdrop()
        ;
    }

    public function store(Request $request): RedirectResponse
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

        return Redirect::route('apps.holidays.edit', $holiday->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function edit(Request $request, Holiday $holiday): Modal
    {
        return Inertia::modal('Default', [
            'form' => $this->__form(),
            'isModal' => true,
            'title' => 'Holiday edition',
            'routes' => [
                'holiday' => [
                    'route' => route('apps.holidays.update', $holiday->id),
                    'method' => 'patch',
                    'buttonClass' => 'justify-end',
                ],
            ],
            'data' => $holiday,
        ])
            ->baseRoute('apps.holidays.index')
            // ->refreshBackdrop()
        ;
    }

    public function update(Request $request, Holiday $holiday): RedirectResponse
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

        return Redirect::route('apps.holidays.edit')->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'toast_count' => 1,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
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

    public function forceDestroy(Request $request): RedirectResponse
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

    public function restore(Request $request): RedirectResponse
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








    public function deleteUserDestroy(Request $request): JsonResponse
    {
        // $this->authorize('access', User::class);
        // $this->authorize('isManager', User::class);
        // $this->authorize('canDestroyOrRestore', [Role::class, $request]);

        $list = collect($request->list)->pluck('id');

        try {
            $total = User::whereIn('id', $list)->delete();

            return response()->json([
                'type' => 'success',
                'title' => 'Users',
                'message' => '{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.',
                'length' => $total,
                'replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on remove selected item.|Error on remove selected items.',
                'length' => count($list),
            ]);
        }
    }

    public function postUserRestore(Request $request): JsonResponse
    {
        // $this->authorize('access', User::class);
        // $this->authorize('isManager', User::class);
        // $this->authorize('canDestroyOrRestore', [Role::class, $request]);

        $list = collect($request->list)->pluck('id');

        try {
            $total = User::whereIn('id', $list)->restore();

            return response()->json([
                'type' => 'success',
                'title' => 'Users',
                'message' => '{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.',
                'length' => $total,
                'replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on remove selected item.|Error on remove selected items.',
                'length' => count($list),
            ]);
        }
    }

    public function deleteUserForceDestroy(Request $request): JsonResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isSuperAdmin', User::class);

        $list = collect($request->list)->pluck('id');

        try {
            $total = User::whereIn('id', $list)->forceDelete();

            return response()->json([
                'type' => 'success',
                'title' => 'Users',
                'message' => '{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.',
                'length' => $total,
                'replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on erase selected item.|Error on erase selected items.',
                'length' => $list,
            ]);
        }
    }

    public function __fields(Request $request): array
    {
        return [
            [
                'type' => 'input',
                'name' => 'name',
                'label' => 'Name',
                'span' => 2,
            ],
            [
                'type' => 'toggle',
                'name' => 'active',
                'label' => 'Active',
                'colorOn' => 'success',
                'colorOff' => 'danger',
            ],
            [
                'type' => 'input',
                'name' => 'email',
                'label' => 'Email',
                'span' => 2,
            ],
            [
                'type' => 'text',
                'name' => 'email_verified_at',
                'label' => 'Email verified at',
                'readonly' => true,
            ],
        ];
    }

    public function index2(Request $request): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Bood4ll', [
            'build' => [
                [
                    'label' => Route::current()->title,
                    'description' => Route::current()->description,
                    'fields' => [
                        [
                            'type' => 'table',
                            'structure' => [
                                'actions' => [
                                    'index' => [
                                        'source' => 'getHolidaysIndex',
                                        'visible' => Gate::allows('apps.holidays.index'),
                                        'disabled' => $request->user()->cannot('isManager', User::class),
                                    ],
                                    'create' => [
                                        'visible' => (
                                            Gate::allows('apps.holidays.store')
                                            && $request->user()->can('isManager', User::class)
                                        ),
                                        'disabled' => $request->user()->cannot('isManager', User::class),
                                        'components' => [
                                            [
                                                'label' => 'Main data',
                                                'description' => 'User account profile information.',
                                                'cols' => 3,
                                                'fields' => $this->__fields($request),
                                                'visible' => (
                                                    Gate::allows('apps.holidays.store')
                                                    && $request->user()->can('isManager', User::class)
                                                ),
                                                'disabled' => $request->user()->cannot('isManager', User::class),
                                                'confirm' => true,
                                                'toastTitle' => 'Add',
                                                'toast' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
                                                'toastClass' => 'success',
                                                'callback' => 'apps.holidays.store',
                                                'method' => 'post',
                                            ],
                                        ],
                                    ],
                                    'edit' => [
                                        'visible' => (
                                            Gate::allows('apps.holidays.update')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                        'disabled' => $request->user()->cannot('isManager', User::class),
                                        'components' => [
                                            [
                                                'source' => [
                                                    'route' => 'getUserInfo',
                                                    'transmute' => ['user' => 'id'],
                                                ],
                                                'label' => 'Main data',
                                                'description' => 'User account profile information.',
                                                'cols' => 3,
                                                'fields' => $this->__fields($request),
                                                'visible' => (
                                                    Gate::allows('apps.holidays.update')
                                                    && $request->user()->can('isManager', User::class)
                                                    && $request->user()->can('canManageNestedData', User::class)
                                                ),
                                                'disabled' => $request->user()->cannot('isManager', User::class),
                                                'confirm' => true,
                                                'toastTitle' => 'Edit',
                                                'toast' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
                                                'toastClass' => 'success',
                                                'callback' => 'apps.holidays.update',
                                                'method' => 'patch',
                                            ],
                                        ],
                                    ],
                                    'destroy' => [
                                        'callback' => 'apps.holidays.destroy',
                                        'method' => 'delete',
                                        'visible' => (
                                            Gate::allows('apps.holidays.destroy')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                    ],
                                    'restore' => [
                                        'callback' => 'apps.holidays.restore',
                                        'method' => 'post',
                                        'visible' => (
                                            Gate::allows('apps.holidays.restore')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                    ],
                                    'forceDestroy' => [
                                        'callback' => 'apps.holidays.forceDestroy',
                                        'method' => 'delete',
                                        'visible' => (
                                            Gate::allows('apps.holidays.forceDestroy')
                                            && $request->user()->can('isManager', User::class)
                                            && $request->user()->can('canManageNestedData', User::class)
                                        ),
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'composite',
                                        'header' => 'Name',
                                        'field' => 'name',
                                        'values' => [
                                            [
                                                'field' => 'name',
                                            ],
                                            [
                                                'field' => 'authority',
                                                'class' => 'text-xs',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'active',
                                        'header' => 'Day off',
                                        'field' => 'day_off',
                                    ],
                                    [
                                        'type' => 'composite',
                                        'header' => 'Duration',
                                        'field' => 'start_at',
                                        'values' => [
                                            [
                                                'field' => 'start_at',
                                                'class' => 'text-xs',
                                            ],
                                            [
                                                'field' => 'end_at',
                                                'class' => 'text-xs',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ]);
    }
}
