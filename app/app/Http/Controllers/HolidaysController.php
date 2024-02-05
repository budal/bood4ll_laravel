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
                                            'type' => 'text',
                                            'title' => 'Name',
                                            'field' => 'name',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Authority',
                                            'field' => 'authority',
                                        ],
                                        [
                                            'type' => 'active',
                                            'title' => 'Day off',
                                            'field' => 'day_off',
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
            ],
        ]);
    }

    public function __form(): array
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

    public function create(Request $request): Modal
    {
        return Inertia::modal('Default', [
            'form' => $this->__form(),
            'isModal' => true,
            'tabs' => false,
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
            'tabs' => false,
            'title' => 'Holiday creation',
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
            ->refreshBackdrop();
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
}
