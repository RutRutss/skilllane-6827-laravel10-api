<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BorrowingResource;
use App\Models\Borrowing;
use App\Models\Book;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    public function borrow(Request $request)
    {
        $bookId = $request->bookId;
        $userId = $request->user()->id;

        $book = Book::where('id', $bookId)->first();

        if (!$book) {
            return response()->json([
                "status" => "error",
                "message" => "Not Found Book Id " . $bookId,
            ], 404);
        }

        $borrowThisBook = Borrowing::where('book_id', $bookId)->orderBy('id', 'desc')->first();

        if ($borrowThisBook && $borrowThisBook->status == 0) {
            return response()->json([
                "status" => "error",
                "message" => "Book Id " . $bookId . " is Borrowing",
            ], 400);
        }

        DB::beginTransaction();
        try {
            $borrow = Borrowing::create([
                "book_id" => $bookId,
                "user_id" => $userId,
                "borrow_date" => now(),
                "return_date" => null
            ]);

            DB::commit();

            return response()->json([
                "status" => "ok",
                "message" => "Borrow Successfully",
                "data" => $borrow,
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

    public function return_book(Request $request)
    {
        $bookId = $request->bookId;

        $book = Book::where('id', $bookId)->first();

        if (!$book) {
            return response()->json([
                "status" => "error",
                "message" => "Not Found Book Id " . $bookId,
            ], 404);
        }

        $borrowThisBook = Borrowing::where('book_id', $bookId)->orderBy('id', 'desc')->first();

        if ($borrowThisBook && $borrowThisBook->status == 0) {

            DB::beginTransaction();
            try {
                $borrowThisBook->update([
                    "return_date" => now(),
                    "status" => 1
                ]);

                DB::commit();

                return response()->json([
                    "status" => "ok",
                    "message" => "Return Successfully",
                    "data" => $borrowThisBook,
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    "status" => "error",
                    "message" => "Fail to Return Book",
                    "error" => $e
                ], 500);
            }
        }
    }

    public function index(Request $request)
    {

        $page = $request->query('page');

        $query = Borrowing::query();

        if ($request->has('bookId')) {
            $query->where('book_id', '=', $request->bookId);
        }

        if ($page) {
            $borrowings = $query->paginate(3);
        } else {
            $borrowings = $query->get();
        }

        $paginate = $page ? [
            "currentPage" => $borrowings->currentPage(),
            "perPage" => $borrowings->perPage(),
            "total" => $borrowings->total(),
            "lastPage" => $borrowings->lastPage(),
            "nextPageUrl" => $borrowings->nextPageUrl(),
            "prevPageUrl" => $borrowings->previousPageUrl(),
        ] : null;

        return response()->json([
            "status" => "ok",
            "message" => "success",
            "datas" => BorrowingResource::collection($borrowings),
            "paginate" => $paginate
        ], 200);
    }
}
