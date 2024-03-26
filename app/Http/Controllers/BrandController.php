<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BrandController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $brands=Brand::paginate(2);
        return $this->successResponse([
            'brands'=>BrandResource::collection($brands),
            'links'=>BrandResource::collection($brands)->response()->getData()->links,
            'meta'=>BrandResource::collection($brands)->response()->getData()->meta,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

            $validator= Validator::make($request->all(), [
                'name'=>'required|string',
                'display_name'=>'required|string',

            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            DB::beginTransaction();
            $brand=Brand::create
            ([
                'name'=>$request->name,
                'display_name'=>$request->display_name,
            ]);

            return $this->successResponse(new BrandResource($brand),201);


    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Brand $brand)
    {
        return $this->successResponse(new BrandResource($brand));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand)
    {
        $validator= Validator::make($request->all(), [
            'name'=>'required|string',
            'display_name'=>'required|string',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        DB::beginTransaction();
        $brand->update
        ([
            'name'=>$request->name,
            'display_name'=>$request->display_name,
        ]);




        DB::commit();
        return $this->successResponse(new BrandResource($brand),200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand)
    {
        DB::beginTransaction();
        $brand->delete();
        DB::commit();
        return $this->successResponse(new BrandResource($brand),200);
    }

    public function products(Brand $brand)
    {
        return $this->successResponse(new BrandResource($brand->load('products')));
    }
}
