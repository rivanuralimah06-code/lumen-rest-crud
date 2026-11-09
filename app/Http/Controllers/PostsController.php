<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostsController extends Controller
{
    public function index()
    {
        $posts = Post::OrderBy("id", "DESC")->paginate(10);

        return response()->json($posts, 200);
    }
}