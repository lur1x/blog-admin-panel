<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexPostRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $postService,
    ) {}

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->create($request->user(), $request->validated());

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }

    public function index(IndexPostRequest $request): AnonymousResourceCollection
    {
        $paginator = $this->postService->getAll($request->validated());

        return PostResource::collection($paginator);
    }

    public function myPosts(IndexPostRequest $request): AnonymousResourceCollection
    {
        $paginator = $this->postService->getUserPosts($request->user(), $request->validated());

        return PostResource::collection($paginator);
    }
}
