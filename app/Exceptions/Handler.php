<?php

namespace App\Exceptions;

use Error;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use App\Http\Controllers\ApiResponse;
class Handler extends ExceptionHandler
{
    protected function errorResponse($message=null,$code): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'=>'falls',
            'message'=>$message,
            'data'=>null
        ], $code);
    }
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $e)
    {
       if ($e instanceof ModelNotFoundException)
       {
           DB::rollBack();
           return $this->errorResponse($e->getMessage(), 404);
       }
        if ($e instanceof NotFoundHttpException)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 404);
        }
        if ($e instanceof MethodNotAllowedHttpException)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
        if ($e instanceof Exception)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }

      if ($e instanceof Error)
      {
          DB::rollBack();
          return $this->errorResponse($e->getMessage(), 500);
      }
        if (config('app.debug'))
        {
            return parent::render($request , $e);
        }
        DB::rollBack();
        return $this->errorResponse($e->getMessage(),500);
    }




}
