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
    public function getHolidaysIndex(Request $request): JsonResponse
    {
        $this->authorize('access', User::class);

        $holidays = Holiday::select('holidays.*')
            ->getDates()
            ->where('name', 'ilike', "%$request->search%")
            ->orderBy('start_at')
            ->when($request->listItems ?? null, function ($query, $listItems) {
                if ($listItems == 'both') {
                    $query->withTrashed();
                } elseif ($listItems == 'trashed') {
                    $query->onlyTrashed();
                }
            })
            ->cursorPaginate(30)
            ->withQueryString();

        return response()->json($holidays);
    }

    public function getHolidayInfo(Request $request, Holiday $holiday): JsonResponse
    {
        $this->authorize('access', User::class);

        return response()->json($holiday);
    }

    public function postHolidayStore(Request $request): JsonResponse
    {
        $this->authorize('access', User::class);

        // $request->validate([
        //     'name' => ['required', 'string', 'max:100', Rule::unique(Role::class)],
        //     'description' => ['nullable', 'string', 'max:255'],
        //     'active' => ['boolean'],
        //     'lock_on_expire' => ['boolean'],
        //     'expires_at' => ['nullable', 'date', 'required_if:lock_on_expire,true'],
        //     'full_access' => ['boolean', 'accepted_if:manage_nested,true'],
        //     'manage_nested' => ['boolean'],
        //     'remove_on_change_unit' => ['boolean'],
        // ], $messages = [
        //     // 'required' => 'The :attribute field is required.',
        // ], $attributes = [
        //     // 'email' => 'email address',
        // ],);

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

            return response()->json([
                'type' => 'error',
                'message' => 'Error on add this item.|Error on add the items.',
                'length' => 1,
            ]);
        }

        DB::commit();

        return response()->json([
            'type' => 'success',
            'title' => 'Holidays',
            'message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'length' => 1,
        ]);
    }

    public function patchHolidayUpdate(Request $request, Holiday $holiday): JsonResponse
    {
        $this->authorize('access', User::class);

        // $request->validate([
        //     'name' => ['required', 'string', 'max:100', Rule::unique(Role::class)->ignore($role->id)],
        //     'description' => ['nullable', 'string', 'max:255'],
        //     'active' => ['boolean'],
        //     'lock_on_expire' => ['boolean'],
        //     'expires_at' => ['nullable', 'date', 'required_if:lock_on_expire,true'],
        //     'full_access' => ['boolean', 'accepted_if:manage_nested,true'],
        //     'manage_nested' => ['boolean'],
        //     'remove_on_change_unit' => ['boolean'],
        // ], $messages = [
        //     // 'required' => 'The :attribute field is required.',
        // ], $attributes = [
        //     // 'email' => 'email address',
        // ],);

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

            return response()->json([
                'type' => 'error',
                'message' => 'Error on edit selected item.|Error on edit selected items.',
                'length' => 1,
            ]);
        }

        DB::commit();

        return response()->json([
            'type' => 'success',
            'title' => 'Users',
            'message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'length' => 1,
        ]);
    }

    public function deleteHolidayDestroy(Request $request): JsonResponse
    {
        // $this->authorize('access', User::class);

        $list = collect($request->list)->pluck('id');

        try {
            $total = Holiday::whereIn('id', $list)->delete();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on remove selected item.|Error on remove selected items.',
                'length' => count($list),
            ]);
        }

        return response()->json([
            'type' => 'success',
            'title' => 'Holidays',
            'message' => '{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.',
            'length' => $total,
            'replacements' => ['total' => $total],
        ]);
    }

    public function postHolidayRestore(Request $request): JsonResponse
    {
        // $this->authorize('access', User::class);

        $list = collect($request->list)->pluck('id');

        try {
            $total = Holiday::whereIn('id', $list)->restore();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on remove selected item.|Error on remove selected items.',
                'length' => count($list),
            ]);
        }

        return response()->json([
            'type' => 'success',
            'title' => 'Holidays',
            'message' => '{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.',
            'length' => $total,
            'replacements' => ['total' => $total],
        ]);
    }

    public function deleteHolidayForceDestroy(Request $request): JsonResponse
    {
        // $this->authorize('access', User::class);
        // $this->authorize('isSuperAdmin', User::class);

        $list = collect($request->list)->pluck('id');

        try {
            $total = Holiday::whereIn('id', $list)->forceDelete();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'type' => 'error',
                'message' => 'Error on erase selected item.|Error on erase selected items.',
                'length' => $list,
            ]);
        }

        return response()->json([
            'type' => 'success',
            'title' => 'Users',
            'message' => '{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.',
            'length' => $total,
            'replacements' => ['total' => $total],
        ]);
    }

    public function __fields(Request $request): array
    {
        return [
            [
                'type' => 'input',
                'name' => 'name',
                'label' => 'Name',
                'span' => 6,
                'required' => true,
                'autofocus' => true,
            ],
            [
                'type' => 'toggle',
                'name' => 'active',
                'label' => 'Active',
                'span' => 2,
                'colorOn' => 'success',
                'colorOff' => 'danger',
            ],
            [
                'type' => 'text',
                'name' => 'authority',
                'label' => 'Authority',
                'span' => 4,
                'required' => true,
            ],
            [
                'type' => 'toggle',
                'name' => 'day_off',
                'label' => 'Day off',
                'span' => 2,
                'colorOn' => 'success',
                'colorOff' => 'danger',
            ],
            [
                'type' => 'toggle',
                'name' => 'easter',
                'label' => 'Easter',
                'span' => 2,
                'colorOn' => 'info',
            ],
            [
                'type' => 'date',
                'name' => 'date',
                'label' => 'Date',
                'span' => 4,
                'required' => true,
            ],
            [
                'type' => 'time',
                'name' => 'start_time',
                'label' => 'Start at',
                'span' => 2,
                'required' => true,
            ],
            [
                'type' => 'time',
                'name' => 'end_time',
                'label' => 'End at',
                'span' => 2,
                'required' => true,
            ],
            [
                'type' => 'text',
                'name' => 'operator',
                'label' => 'Operator',
                'span' => 2,
                'required' => true,
            ],
            [
                'type' => 'text',
                'name' => 'difference_start',
                'label' => 'Start of difference',
                'span' => 3,
                'required' => true,
            ],
            [
                'type' => 'text',
                'name' => 'difference_end',
                'label' => 'End of difference',
                'span' => 3,
                'required' => true,
            ],
        ];
    }

    public function index(Request $request): Response
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
                                                'description' => 'Define which dates will be holidays and optional points.',
                                                'cols' => 8,
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
                                                    'route' => 'getHolidayInfo',
                                                    'transmute' => ['holiday' => 'id'],
                                                ],
                                                'label' => 'Main data',
                                                'description' => 'Define which dates will be holidays and optional points.',
                                                'cols' => 8,
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
