<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
          'id'=>$this->id,
          'name'=>$this->name,
          'parent_id'=>$this->parent_id,
          'description'=>$this->description,
          'sub_category'=>CategoryResource::collection($this->whenLoaded('sub_category')),
          'parent_category'=>new CategoryResource($this->whenLoaded('parent_category')),
            'products'=>ProductResource::collection($this->whenLoaded('products',function ()
            {
                return $this->products->load('images');
            }))
        ];
    }
}
