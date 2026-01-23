<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        // index & show public, lainnya auth
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $posts = Post::active()
            ->with('user')
            ->paginate(20);

        return response()->json($posts);
    }

    public function create()
    {
        return 'posts.create';
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
        ]);

        $data['user_id'] = $request->user()->id;

        $post = Post::create($data);

        return response()->json($post, 201);
    }

    public function show(Post $post)
    {
        if (!$post->published_at || $post->published_at->isFuture()) {
            abort(404);
        }

        return response()->json(
            $post->load('user')
        );
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return 'posts.edit';
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
        ]);

        $post->update($data);

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(null, 204);
    }
}
