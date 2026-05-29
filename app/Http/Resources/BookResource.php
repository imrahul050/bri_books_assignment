<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
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
            'id'            => $this->id,
            'title'         => $this->title,
            'author'        => $this->author,
            'coverImage'    => $this->cover_image ? asset('uploads/books/' . $this->cover_image) : '',
            'price'         => $this->price,
            'publishedDate' => $this->published_date?->format('Y-m-d') ?? '',
            'createdAt'     => $this->created_at?->format('d-m-Y H:i:s') ?? '',
            'updatedAt'     => $this->updated_at?->format('d-m-Y H:i:s') ?? '',
        ];
    }
}
