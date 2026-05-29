<?php

namespace App\Repositories;

use App\Models\Book;
use App\Traits\FileUpload;

class BookRepository extends BaseRepository
{
    use FileUpload;

    public function list($request)
    {
        $query = Book::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);

        return $query->latest()->paginate($perPage);
    }

    public function store($request)
    {
        $data = $request->only(['title', 'author', 'price', 'published_date']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->handleSingleFileUpload(
                $request->file('cover_image'),
                public_path('uploads/books')
            );
        }

        return Book::create($data);
    }

    public function find($id)
    {
        return Book::find($id);
    }

    public function update($request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return null;
        }

        $data = $request->only(['title', 'author', 'price', 'published_date']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->handleSingleFileUpload(
                $request->file('cover_image'),
                public_path('uploads/books')
            );
        }

        $book->update($data);

        return $book->fresh();
    }

    public function destroy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return null;
        }

        $book->_deleted = 1;
        $book->save();

        return $book;
    }
}
