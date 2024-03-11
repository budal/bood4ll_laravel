<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Exceptions\InvalidOrderException;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected $withoutDuplicates = true;

    public function register(): void
    {
        // $this->reportable(function (\Throwable $e) {
        // });

        $this->renderable(function (InvalidOrderException $e, Request $request) {
            return response()->json(
                [
                    'errors' => [
                        'status' => 401,
                        'message' => 'Unauthenticated',
                    ]
                ],
                401
            );
        });
    }
}
