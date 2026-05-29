<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Repositories\BookRepository;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    use ApiResponser;

    protected $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $books = $this->bookRepository->list($request);

            return $this->successResponse(
                new BookCollection($books),
                'Books fetched successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse([], $e->getMessage(), 500);
        }
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        try {
            $book = $this->bookRepository->store($request);

            return $this->successResponse(
                new BookResource($book),
                'Book created successfully.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse([], $e->getMessage(), 500);
        }
    }

    public function show($id): JsonResponse
    {
        $book = $this->bookRepository->find($id);

        if (!$book) {
            return $this->errorResponse([], 'Book not found.', 404);
        }

        return $this->successResponse(
            new BookResource($book),
            'Book fetched successfully.'
        );
    }

    public function update(UpdateBookRequest $request, $id): JsonResponse
    {
        try {
            $book = $this->bookRepository->update($request, $id);

            if (!$book) {
                return $this->errorResponse([], 'Book not found.', 404);
            }

            return $this->successResponse(
                new BookResource($book),
                'Book updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse([], $e->getMessage(), 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $book = $this->bookRepository->destroy($id);

        if (!$book) {
            return $this->errorResponse([], 'Book not found.', 404);
        }

        return $this->successResponse([], 'Book deleted successfully.');
    }
}
