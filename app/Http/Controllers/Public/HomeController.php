<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredCourses = Course::query()
            ->published()
            ->with('category')
            ->orderByDesc('is_featured')
            ->latest()
            ->limit(6)
            ->get();

        $stats = [
            'courses' => Course::query()->published()->count(),
        ];

        return view('public.home', compact('featuredCourses', 'stats'));
    }
}
