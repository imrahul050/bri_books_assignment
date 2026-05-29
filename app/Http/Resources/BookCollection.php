<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BookCollection extends ResourceCollection
{
    /**
     * The "data" wrapper key.
     *
     * @var string|null
     */
    public $collects = BookResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'books' => $this->collection,
            'links' => [
                'has-pages'    => $this->hasMorePages() ?? false,
                'next'         => $this->nextPageUrl() ?? '',
                'items'        => $this->count(),
                'total'        => $this->total(),
                'current_page' => $this->currentPage(),
                'last_page'    => $this->lastPage(),
                'per_page'     => $this->perPage(),
            ],
        ];
    }
}
