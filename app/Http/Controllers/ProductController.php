<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Product_image;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products=Product::paginate(10);
        return $this->successResponse([
            'products'=>ProductResource::collection($products->load('images')),
            'links'=>ProductResource::collection($products)->response()->getData()->links,
            'meta'=>ProductResource::collection($products)->response()->getData()->meta,
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
         'brand_id'=>'required|integer',
         'category_id'=>'required|integer',
           'primary_image'=>'required|image',
         'price'=>'integer',
         'quantity'=>'integer',
         'delivery_amount'=>'integer',
         'description'=>'required',
         'images.*'=>'nullable|image'
       ]);
       if ($validator->fails()) {
           return $this->errorResponse($validator->messages(),422);
       }

        DB::beginTransaction();

        ///load image
        $primaryImageName=Carbon::now()->microsecond.'.'.$request->primary_image->extension();
        $request->primary_image->storeAs('images/products',$primaryImageName,'public');

        ///load images
        if ($request->has('images'))
        {
            $fileNameImages=[];
            foreach ($request->images as $image)
            {
                $fileNameImage=Carbon::now()->microsecond.'.'.$image->extension();
                $image->storeAs('images/products',$fileNameImage,'public');
                array_push($fileNameImages,$fileNameImage);
            }
        }
        ///create products

        $product=Product::create([
            'name'=>$request->name,
            'brand_id'=>$request->brand_id,
            'category_id'=>$request->category_id,
            'primary_image'=>$primaryImageName,
            'price'=>$request->price,
            'quantity'=>$request->quantity,
            'delivery_amount'=>$request->delivery_amount,
            'description'=>$request->description,
        ]);
        ///create product-images
        ///
        ///
        ///

        if ($request->has('images')){
            foreach ($fileNameImages as $fileNameImage)
            {
                Product_image::create([
                    'product_id'=>$product->id,
                    'image'=>$fileNameImage,

                ]);
            }
    }
        DB::commit();
        return $this->successResponse(new ProductResource($product),201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $this->successResponse(new ProductResource($product->load('images')));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $validator= Validator::make($request->all(), [
            'name'=>'required|string',
            'brand_id'=>'required|integer',
            'category_id'=>'required|integer',
            'primary_image'=>'nullable|image',
            'price'=>'integer',
            'quantity'=>'integer',
            'delivery_amount'=>'integer',
            'description'=>'required',
            'images.*'=>'nullable|image'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(),422);
        }

        DB::beginTransaction();

        ///load image
        if ($request->has('primary_image'))
        {
            $primaryImageName=Carbon::now()->microsecond.'.'.$request->primary_image->extension();
            $request->primary_image->storeAs('images/products',$primaryImageName,'public');
        }


        ///load images
        if ($request->has('images'))
        {
            $fileNameImages=[];
            foreach ($request->images as $image)
            {
                $fileNameImage=Carbon::now()->microsecond.'.'.$image->extension();
                $image->storeAs('images/products',$fileNameImage,'public');
                array_push($fileNameImages,$fileNameImage);
            }
        }
        ///create products

        $product->update([
            'name'=>$request->name,
            'brand_id'=>$request->brand_id,
            'category_id'=>$request->category_id,
            'primary_image'=>$request->has('primary_image')?$primaryImageName:$product->primary_image,
            'price'=>$request->price,
            'quantity'=>$request->quantity,
            'delivery_amount'=>$request->delivery_amount,
            'description'=>$request->description,
        ]);
        ///create product-images
        ///
        ///
        ///

        if ($request->has('images')){
            ///delete before images
            foreach ($product->images as $productImage)
            {
                $productImage->delete();
            }
            foreach ($fileNameImages as $fileNameImage)
            {
                Product_image::create([
                    'product_id'=>$product->id,
                    'image'=>$fileNameImage,

                ]);
            }
        }
        DB::commit();
        return $this->successResponse(new ProductResource($product),200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        $product->delete();
        DB::commit();
        return $this->successResponse(new ProductResource($product),200);
    }
}
