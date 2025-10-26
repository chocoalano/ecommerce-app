<?php
namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Article;

class ArticleIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Article::query()
            ->where('is_published', true)
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%');
            });
        // Contoh relasi kategori (jika ada)
        if ($this->category) {
            $query->whereHas('categories', function($q) {
                $q->where('slug', $this->category);
            });
        }
        $articles = $query->orderByDesc('published_at')->paginate(9);
        return view('livewire.article-index', compact('articles'));
    }
}
