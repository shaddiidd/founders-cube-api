<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Auth;

class BlogLikeController extends Controller
{
    // Like
    public function store(Request $request, $id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        if ($request->user()->likedBlogs->contains($blog)) {
            return response()->json(['message' => 'You already liked this blog'], 400);
        }

        $request->user()->likedBlogs()->attach($blog);

        return response()->json(['message' => 'Blog liked successfully']);
    }

    // Unlike
    public function destroy(Request $request, $id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        if (!$request->user()->likedBlogs->contains($blog)) {
            return response()->json(['message' => 'You have not liked this blog'], 400);
        }

        $request->user()->likedBlogs()->detach($blog);

        return response()->json(['message' => 'Blog unliked successfully']);
    }
}
