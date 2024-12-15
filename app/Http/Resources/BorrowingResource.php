<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "bookId" => $this->book_id,
            "bookName" => $this->book->name,
            "userId" => $this->user_id,
            "userName" => $this->user->name,
            "borrowDate" => $this->borrow_date,
            "returnDate" => $this->return_date,
            "status" => $this->status == 0 ? "ยังไม่คืน" : "คืนแล้ว"
        ];
    }
}
