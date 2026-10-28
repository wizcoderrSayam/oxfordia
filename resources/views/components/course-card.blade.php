@props(['course'])
@php($enquireUrl = route('admissions.create', ['course' => $course->slug]))
<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1.5 hover:border-amber-300 hover:shadow-xl">
    <div class="relative block aspect-[16/10] overflow-hidden bg-slate-100">
        <img src="{{ $course->image_url }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-110">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-navy-950/60 via-transparent to-transparent opacity-0 transition duration-300 group-hover:opacity-100"></div>
        @if ($course->category)
            <span class="absolute top-3 left-3 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-navy-800 shadow-sm">{{ $course->category->name }}</span>
        @endif
        @if ($course->is_featured)
            <span class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-amber-500 px-2.5 py-1 text-xs font-bold text-navy-950 shadow-sm"><x-icon name="star" class="size-3" /> Featured</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <div class="mb-5">
            <p class="text-xs font-semibold tracking-wide text-slate-400 uppercase">{{ $course->course_code }}</p>
            <h3 class="mt-1 font-display text-lg leading-snug font-bold text-slate-900">{{ $course->title }}</h3>
            @if ($course->short_description)
                <p class="mt-2 mb-2 line-clamp-3 text-sm text-slate-600">{{ $course->short_description }}</p>
            @endif
        </div>
        <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-4 text-sm">
            <span class="inline-flex items-center gap-1.5 text-slate-500"><x-icon name="clock" class="size-4" /> {{ $course->duration_label ?? 'Flexible' }}</span>
            <a href="{{ $enquireUrl }}" class="btn-accent px-4 py-2 text-sm" aria-label="Enquire about {{ $course->title }}">Enquire Now</a>
        </div>
    </div>
</article>
