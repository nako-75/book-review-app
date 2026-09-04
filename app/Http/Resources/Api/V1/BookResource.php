<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'price' => $this->price,
            'detail' => $this->detail,

            // ジャンル情報（一覧・詳細共通）
            'genres' => GenreResource::collection($this->whenLoaded('genres')),

            // 【一覧用】平均評価・レビュー件数（ロードされているときだけ表示）
            'reviews_avg_rating' => $this->whenNotNull($this->reviews_avg_rating),
            'reviews_count' => $this->whenNotNull($this->reviews_count),

            // 【詳細用】レビュー一覧（投稿者名・評価・コメント・投稿日時）
            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'user_name' => $review->user->name ?? '不明',
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at,
                    ];
                });
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
