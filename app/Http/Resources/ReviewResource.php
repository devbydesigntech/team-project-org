<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'reviewer_id' => $this->reviewer_id,
            'visible_reviewer_name' => $this->visibleReviewerName($request->user()),
            'project_id' => $this->project_id,
            'project' => [
                'id' => $this->project->id,
                'name' => $this->project->name,
            ],
            'reviewee_user_id' => $this->reviewee_user_id,
            'reviewee' => $this->when($this->reviewee, [
                'id' => $this->reviewee?->id,
                'name' => $this->reviewee?->name,
            ]),
            'rating' => $this->rating,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
