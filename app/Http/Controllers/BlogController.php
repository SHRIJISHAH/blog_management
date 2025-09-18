<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Http\Resources\BlogResource;
use App\Http\Resources\BlogCollection;

class BlogController extends Controller
{
    public function store(StoreBlogRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('blogs', 'public');
            }

            $data['user_id'] = auth()->id();

            $blog = Blog::create($data);
            $blog->load('user');
 
            return response()->json([
                'message' => 'Blog created successfully',
                'blog' => new BlogResource($blog)
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create blog'], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Blog::withCount('likes')->with('user');

        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title','like',"%$search%")
                  ->orWhere('description','like',"%$search%");
            });
        }

        if ($request->query('sort') == 'most_liked') {
            $query->orderBy('likes_count','desc');
        } else {
            $query->latest();
        }

        $blogs = $query->paginate($request->get('per_page', 5));

        return new BlogCollection($blogs);
    }

    public function show($id)
    {
        try {
            $blog = Blog::withCount('likes')
                    ->with('user')
                    ->findOrFail($id);
                    
            return response()->json([
                'message' => 'Blog retrieved successfully',
                'blog' => new BlogResource($blog)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Blog not found'], 404);
        }
    }

    public function update(UpdateBlogRequest $request, $id)
    {
        try {
            $blog = Blog::where('user_id', auth()->id())->findOrFail($id);

            $data = $request->validated();

            if ($request->hasFile('image')) {
                if ($blog->image) {
                    Storage::disk('public')->delete($blog->image);
                }
                $data['image'] = $request->file('image')->store('blogs', 'public');
            }

            $blog->update($data);
            $blog->load('user');
            
            return response()->json([
                'message' => 'Blog updated successfully',
                'blog' => new BlogResource($blog)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Blog not found or unauthorized'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update blog'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $blog = Blog::where('user_id', auth()->id())->findOrFail($id);
            
            if ($blog->image) {
                Storage::disk('public')->delete($blog->image);
            }
            
            $blog->delete();
            
            return response()->json(['message' => 'Blog deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Blog not found or unauthorized'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete blog'], 500);
        }
    }

    public function toggleLike($id)
    {
        try {
            $blog = Blog::findOrFail($id);
            $like = $blog->likes()->where('user_id', auth()->id())->first();

            if ($like) {
                $like->delete();
                $message = 'Blog unliked successfully';
                $liked = false;
            } else {
                $blog->likes()->create(['user_id' => auth()->id()]);
                $message = 'Blog liked successfully';
                $liked = true;
            }

            return response()->json([
                'message' => $message,
                'liked' => $liked,
                'likes_count' => $blog->likes()->count()
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Blog not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to toggle like'], 500);
        }
    }
}