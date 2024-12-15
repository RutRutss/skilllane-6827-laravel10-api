<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page');

        $query = Book::query();

        if ($request->has('name')) {
            $query->where('name', 'like', "%" . $request->name . "%");
        }

        if ($request->has('author')) {
            $query->where('author', 'like', "%" . $request->author . "%");
        }

        if ($page) {
            $books = $query->paginate(10);
        } else {
            $books = $query->get();
        }

        $paginate = $page ? [
            "currentPage" => $books->currentPage(),
            "perPage" => $books->perPage(),
            "total" => $books->total(),
            "lastPage" => $books->lastPage(),
            "nextPageUrl" => $books->nextPageUrl(),
            "prevPageUrl" => $books->previousPageUrl(),
        ] : null;

        return response()->json([
            "status" => "ok",
            "message" => "success",
            "datas" => BookResource::collection($books),
            "paginate" => $paginate
        ], 200);
    }

    public function show($id)
    {
        $book = Book::where('id', $id)->first();

        if ($book) {
            return response()->json([
                "status" => "ok",
                "message" => "success",
                "data" => new BookResource($book),
            ], 200);
        } else {
            return response()->json([
                "status" => "error",
                "message" => "Not Found Book Id " . $id,
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $name = $request->name;
        $category = $request->category;
        $author = $request->author;

        DB::beginTransaction();

        try {
            $book = Book::create([
                'name' => $name,
                'category' => $category,
                'author' => $author,
            ]);

            DB::commit();

            return response()->json([
                "status" => "ok",
                "message" => "Insert New Book Successfully",
                "data" => new BookResource($book),
            ], 201);
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                "status" => "error",
                "message" => "Fail to Insert New Book",
                "error" => $e
            ], 500);
        }
    }

    public function update_all(Request $request, $id)
    {
        $book = Book::where('id', $id)->first();

        if ($book) {

            DB::beginTransaction();
            try {
                $data = [
                    'name' => $request->bookName,
                    'category' => $request->category,
                    'author' => $request->author,
                ];

                $book->update($data);

                DB::commit();

                return response()->json([
                    "status" => "ok",
                    "message" => "Update All Field Success",
                    "data" => new BookResource($book),
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    "status" => "error",
                    "message" => "Fail to Update Book",
                    "error" => $e
                ], 500);
            }
        } else {
            return response()->json([
                "status" => "error",
                "message" => "Not Found Book Id " . $id,
            ], 404);
        }
    }

    public function update_partial(Request $request, $id)
    {
        $book = Book::where('id', $id)->first();

        if ($book) {

            DB::beginTransaction();
            try {
                $data = array_filter([
                    'name' => $request->bookName ?? null,
                    'category' => $request->category ?? null,
                    'author' => $request->author ?? null,
                ], fn($value) => !is_null($value));

                $book->update($data);

                DB::commit();

                return response()->json([
                    "status" => "ok",
                    "message" => "Update All Field Success",
                    "data" => new BookResource($book),
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    "status" => "error",
                    "message" => "Fail to Update Book",
                    "error" => $e
                ], 500);
            }
        } else {
            return response()->json([
                "status" => "error",
                "message" => "Not Found Book Id " . $id,
            ], 404);
        }
    }

    public function delete($id)
    {
        $book = Book::where('id', $id)->first();

        if ($book) {
            DB::beginTransaction();
            try {
                $book->delete();

                DB::commit();

                return response()->json([
                    "status" => "ok",
                    "message" => "Delete Book ID " . $id . " Success",
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    "status" => "error",
                    "message" => "Fail to Update Book",
                    "error" => $e
                ], 500);
            }
        } else {
            return response()->json([
                "status" => "error",
                "message" => "Not Found Book Id " . $id,
            ], 404);
        }
    }
}
