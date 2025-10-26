<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        // Filter by title
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Filter by published status
        if ($request->filled('published')) {
            $query->where('published', $request->input('published'));
        }

        $query->orderBy("created_at", "desc");

        if ($request->ajax()) {
            $page = $request->input('page', 1);
            $articles = $query->paginate(10, ['*'], 'page', $page);
            return response()->json([
                'articles' => view('pages.articles.article_list', compact('articles'))->render(),
                'next_page_url' => $articles->nextPageUrl(),
            ]);
        } else {
            $articles = $query->paginate(10);
            return view('pages.articles.index', compact('articles'));
        }
    }

    public function show($slug)
    {
        $article = Article::whereSlug($slug)->firstOrFail();
        $reccomendationArticles = Article::where('id', '!=', $article->id)
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();
        return view('pages.articles.show', compact('article', 'reccomendationArticles'));
    }

}
