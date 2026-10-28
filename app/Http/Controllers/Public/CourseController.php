<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $categorySlug = (string) $request->query('category', '');

        $courses = Course::query()
            ->published()
            ->with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('course_code', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->when($categorySlug !== '', fn ($query) => $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug)))
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->paginate(9)
            ->withQueryString();

        $categories = CourseCategory::query()
            ->whereHas('courses', fn ($query) => $query->published())
            ->orderBy('name')
            ->get();

        return view('public.courses.index', compact('courses', 'categories', 'search', 'categorySlug'));
    }

    /**
     * Course detail pages were retired; old links now open the admission enquiry for that course.
     */
    public function show(string $slug): RedirectResponse
    {
        return redirect()->route('admissions.create', ['course' => $slug], 301);
    }
}
