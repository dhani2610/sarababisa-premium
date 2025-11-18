<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\SubCategory;
use Livewire\Component;
use Livewire\WithPagination;

class SubKategoriData extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::whereNot('category_name', 'Handphone')->get();
        $sub_categories_count = SubCategory::where('cabang_id',getCabangId())->get()->count();
        return view('livewire.sub-kategori-data', [
            'categories' => $categories,
            'sub_categories_count' => $sub_categories_count,
            'sub_categories' => $this->search === null ?
                SubCategory::where('cabang_id',getCabangId())->latest()->paginate($this->paginate) :
                SubCategory::where('cabang_id',getCabangId())->latest()->where('name', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ]);
    }
}
