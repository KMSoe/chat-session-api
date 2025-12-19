<?php
namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Exception $e, Request $request) {
            if ($request->is('api/*')) {
                if ($e instanceof \League\OAuth2\Server\Exception\OAuthServerException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Session expired',
                    ], 419);
                }

                if ($e instanceof PostTooLargeException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'File is too big or invalid',
                    ], 422);
                }
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Not Found',
                    ], 404);
                }
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Unauthenticated',
                    ], 401);
                }

                if ($e instanceof UnauthorizedException || $e instanceof AccessDeniedHttpException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Unauthorized',
                    ], 401);
                }

                if ($e instanceof CustomException) {
                    return response()->json([
                        "code"    => $e->getCustomCode(),
                        'message' => "Something Went Wrong!",
                    ], $e->getCode());
                }

                if ($e instanceof HttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                    ], $e->getStatusCode());
                }
            }
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'status'  => false,
            'message' => "Unauthenticated.",
        ], 401);
    }

}
