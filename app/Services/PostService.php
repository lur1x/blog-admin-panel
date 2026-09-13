<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PostService
{
    public function create(User $user, array $data): Post
    {
        return $user->posts()->create([
            'title' => $data['title'],
            'text' => $data['text'],
        ]);
    }

    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = Post::query()->with('user');

        return $this->applyFilters($query, $filters)->paginate(
            perPage: $filters['limit'],
            page: $this->pageFromOffset($filters['limit'], $filters['offset']),
        );
    }

    public function getUserPosts(User $user, array $filters): LengthAwarePaginator
    {
        $query = Post::query()
            ->where('user_id', $user->id)
            ->with('user');

        return $this->applyFilters($query, $filters)->paginate(
            perPage: $filters['limit'],
            page: $this->pageFromOffset($filters['limit'], $filters['offset']),
        );
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        $this->applySorting($query, $filters['sort']);
        $this->applyDateFilter($query, $filters);

        return $query;
    }

    private function applySorting(Builder $query, string $sort): void
    {
        match ($sort) {
            'date_asc' => $query->orderBy('created_at', 'asc'),
            'date_desc' => $query->orderBy('created_at', 'desc'),
            'title_asc' => $query->orderBy('title', 'asc'),
            'title_desc' => $query->orderBy('title', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    private function applyDateFilter(Builder $query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
    }

    private function pageFromOffset(int $limit, int $offset): int
    {
        return (int) floor($offset / $limit) + 1;
    }
}
