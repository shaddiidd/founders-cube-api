<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::where('status', 'active')
                     ->withCount('likers')
                     ->get();
        return response()->json($blogs, 200);
    }
    

    public function pendingBlogs()
    {
        $blogs = Blog::where('status', 'pending')->get();
        return response()->json($blogs, 200);
    }

    public function approve($id)
    {
        $blog = Blog::find($id);
        $blog->update(['status' => 'active']);
    }

    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->editor) {
            return response()->json(['message' => 'You don\'t have permission to post'], 403);
        }

        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            // 'image_url' => 'nullable|url',
            // 'image_thumbnail_url' => 'nullable|url',
            'categories' => 'nullable|array',
        ]);

        $slug = strtolower($request->title);
        $slug = preg_replace('/[^\w\s]+/', '', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = trim($slug, '-');
    
        $blog = $request->user()->blogs()->create([
            'title' => $request->title,
            'status' => $request->user()->user_type === 'admin' ? 'active' : 'pending',
            'slug' => $slug,
            'content' => $request->content,
            'image_url' => 'image_url.com',
            'image_thumbnail_url' => 'image_url.com',
            'categories' => $request->categories,
        ]);

        return response()->json([
            'message' => 'Blog created successfully',
            'blog' => $blog
        ], 201);
    }

    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->withCount('likers')->first();
    
        if ($blog) {
            return response()->json($blog, 200);
        } else {
            return response()->json(['message' => 'Blog not found'], 404);
        }
    }
    

    public function update(Request $request, $id)
    {
        $blog = Blog::find($id);

        if ($blog && $blog->user_id == $request->user()->id) {
            $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
                'categories' => 'nullable|array',
            ]);

            $slug = strtolower($request->title);
            $slug = preg_replace('/[^\w\s]+/', '', $slug);
            $slug = preg_replace('/\s+/', '-', $slug);
            $slug = trim($slug, '-');

            $blog->update([
                'title' => $request->title,
                'slug' => $slug,
                'content' => $request->content,
                'categories' => $request->categories,
                'image_url' => 'image_url.com',
                'image_thumbnail_url' => 'image_url.com',
            ]);

            return response()->json([
                'message' => 'Blog updated successfully',
                'blog' => $blog
            ], 200);
        } else {
            return response()->json(['message' => 'Blog not found or unauthorized'], 404);
        }
    }


    public function destroy(Request $request, $id)
    {
        $blog = Blog::find($id);

        if ($blog && $blog->user_id == $request->user()->id) {
            $blog->delete();
            return response()->json(['message' => 'Blog deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Blog not found'], 404);
        }
    }
}
