<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseCategoryController extends Controller
{
    public function index(): View
    {
        $categories = CourseCategory::query()->withCount('courses')->orderBy('name')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new CourseCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = new CourseCategory($this->validated($request));
        $category->save();

        return redirect()->route('admin.categories.index')->with('success', "Category \"{$category->name}\" created.");
    }

    public function edit(CourseCategory $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, CourseCategory $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('success', "Category \"{$category->name}\" updated.");
    }

    public function destroy(CourseCategory $category): RedirectResponse
    {
        if ($category->courses()->exists()) {
            return back()->with('error', 'This category still has courses. Move them to another category first.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?CourseCategory $category = null): array
    {
        $request->merge(['slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name')))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('course_categories', 'name')->ignore($category)],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('course_categories', 'slug')->ignore($category)],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
