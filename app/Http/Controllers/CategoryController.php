<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories=Category::paginate(2);
        return $this->successResponse([
            'categories'=>CategoryResource::collection($categories),
            'links'=>CategoryResource::collection($categories)->response()->getData()->links,
            'meta'=>CategoryResource::collection($categories)->response()->getData()->meta,
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
            'parent_id'=>'required',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        DB::beginTransaction();
        $category=Category::create
        ([
            'name'=>$request->name,
            'parent_id'=>$request->parent_id,
            'description'=>$request->description,
        ]);
        DB::commit();
        return $this->successResponse(new CategoryResource($category),201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return $this->successResponse(new CategoryResource($category));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validator= \Validator::make($request->all(), [
            'name'=>'required|string',
            'parent_id'=>'required|integer',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        DB::beginTransaction();
        $category->update
        ([
            'name'=>$request->name,
            'parent_id'=>$request->parent_id,
            'description'=>$request->description,
        ]);




        DB::commit();
        return $this->successResponse(new CategoryResource($category),200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        DB::beginTransaction();
        $category->delete();
        DB::commit();
        return $this->successResponse(new CategoryResource($category),200);
    }
    ///sub category route
    public function sub_category(Category $category)
    {
        return $this->successResponse(new CategoryResource($category->load('sub_category')));
    }
    ///parent category route
    public function parent_category(Category $category)
    {
        return $this->successResponse(new CategoryResource($category->load('parent_category')));
    }
    public function products(Category $category)
    {
        return $this->successResponse(new CategoryResource($category->load('products')));

    }

}
