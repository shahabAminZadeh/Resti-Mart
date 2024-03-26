<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function successResponse($data,$code = 200 , $message=null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'=>'success',
            'message'=>$message,
            'data'=>$data
        ],$code);
    }
    protected function errorResponse($message=null,$code): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'=>'success',
            'message'=>$message,
            'data'=>null
        ], $code);
    }
}
