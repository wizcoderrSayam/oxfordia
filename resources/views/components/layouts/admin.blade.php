@props(['title' => 'Dashboard'])
@php
    $user = auth()->user();
    $counts = $adminCounts ?? [];
    $sections = [
        'Overview' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
        ],
        'Academics' => [
            ['label' => 'Courses', 'route' => 'admin.courses.index', 'active' => ['admin.courses.*', 'admin.modules.*'], 'icon' => 'book-open'],
            ['label' => 'Categories', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'icon' => 'tags'],
            ['label' => 'Students', 'route' => 'admin.students.index', 'active' => 'admin.students.*', 'icon' => 'users'],
            ['label' => 'Employees', 'route' => 'admin.employees.index', 'active' => 'admin.employees.*', 'icon' => 'id-card'],
            ['label' => 'Enrollments', 'route' => 'admin.enrollments.index', 'active' => 'admin.enrollments.*', 'icon' => 'clipboard-list'],
            ['label' => 'Certificates', 'route' => 'admin.certificates.index', 'active' => 'admin.certificates.*', 'icon' => 'award', 'badge' => $counts['certificates'] ?? 0],
        ],
        'Front office' => [
            ['label' => 'Admission enquiries', 'route' => 'admin.enquiries.index', 'active' => 'admin.enquiries.*', 'icon' => 'inbox', 'badge' => $counts['enquiries'] ?? 0],
            ['label' => 'Contact messages', 'route' => 'admin.messages.index', 'active' => 'admin.messages.*', 'icon' => 'message-square', 'badge' => $counts['messages'] ?? 0],
        ],
    ];

    $administration = array_values(array_filter([
        $user->can('manage-users') ? ['label' => 'Users', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'user'] : null,
        $user->can('manage-settings') ? ['label' => 'Grading', 'route' => 'admin.grading.edit', 'active' => 'admin.grading.*', 'icon' => 'graduation-cap'] : null,
        $user->can('manage-settings') ? ['label' => 'Settings', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'icon' => 'settings'] : null,
        $user->can('view-activity-log') ? ['label' => 'Activity log', 'route' => 'admin.activity.index', 'active' => 'admin.activity.*', 'icon' => 'activity'] : null,
    ]));

    if ($administration) {
        $sections['Administration'] = $administration;
    }

    $createActions = array_values(array_filter([
        ['label' => 'New student', 'hint' => 'Register a student', 'url' => route('admin.students.create'), 'icon' => 'user-plus'],
        ['label' => 'New employee', 'hint' => 'Register a staff member', 'url' => route('admin.employees.create'), 'icon' => 'id-card'],
        ['label' => 'New enrollment', 'hint' => 'Enroll a student in a course', 'url' => route('admin.enrollments.create'), 'icon' => 'clipboard-list'],
        ['label' => 'New course', 'hint' => 'Add to the catalogue', 'url' => route('admin.courses.create'), 'icon' => 'book-open'],
        ['label' => 'New category', 'hint' => 'Group related courses', 'url' => route('admin.categories.create'), 'icon' => 'tags'],
        $user->can('manage-users') ? ['label' => 'New user', 'hint' => 'Give a colleague access', 'url' => route('admin.users.create'), 'icon' => 'user'] : null,
    ]));

    $paletteConfig = [
        'searchUrl' => route('admin.search'),
        'homeUrl' => route('home'),
        'actions' => array_map(fn (array $action): array => ['title' => $action['label'], 'subtitle' => $action['hint'], 'url' => $action['url'], 'icon' => $action['icon']], $createActions),
        'pages' => collect($sections)->flatMap(fn (array $items, string $heading) => array_map(fn (array $item): array => [
            'title' => $item['label'],
            'subtitle' => $heading,
            'url' => route($item['route']),
            'icon' => $item['icon'],
        ], $items))->values()->all(),
    ];

    $initials = collect(explode(' ', trim($user->name)))->filter()->take(2)->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ $title }} | {{ $institute->get('name') }} Admin</title>
    <script>
        try {
            var theme = localStorage.getItem('admin-theme');
            if (theme === 'dark' || (! theme && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
            if (localStorage.getItem('admin-sidebar') === 'collapsed') {
                document.documentElement.dataset.sidebar = 'collapsed';
            }
        } catch (e) {}
    </script>
    <style>html { background: #f5f6f8; } html.dark { background: #0b0f17; color-scheme: dark; }</style>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-ui min-h-screen font-sans text-slate-800 antialiased"
      x-data="{ sidebar: false, desktop: window.matchMedia('(min-width: 1024px)').matches }"
      x-init="window.matchMedia('(min-width: 1024px)').addEventListener('change', event => { desktop = event.matches; if (desktop) sidebar = false });
              $watch('sidebar', open => $nextTick(() => open ? $refs.sidebar.querySelector('a, button')?.focus() : $refs.menuButton?.focus()))"
      @keydown.escape.window="sidebar = false">
    <a href="#main" class="sr-only z-50 rounded-lg bg-white px-4 py-2 text-sm font-medium focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

    <x-flash />

    {{-- Mobile overlay --}}
    <div x-show="sidebar" x-cloak x-transition.opacity class="fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-sm lg:hidden" @click="sidebar = false"></div>

    <aside id="admin-sidebar" x-ref="sidebar"
           class="admin-sidebar fixed inset-y-0 left-0 z-40 flex -translate-x-full flex-col transition-[transform,width] duration-200 ease-out lg:translate-x-0"
           :class="sidebar ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'"
           :inert="(! sidebar && ! desktop) || $store.palette.open" aria-label="Admin navigation">
        <div class="flex shrink-0 items-center gap-2 px-3 pt-4 pb-3">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand-card" title="{{ $institute->get('name') }}">
                @if ($institute->logoUrl())
                    <img src="{{ $institute->logoUrl() }}" alt="{{ $institute->get('name') }}" class="admin-brand-logo">
                @else
                    <span class="admin-monogram" aria-hidden="true">{{ mb_strtoupper(mb_substr($institute->get('name'), 0, 1)) }}</span>
                    <span class="sidebar-text min-w-0 leading-tight">
                        <span class="admin-brand-name">{{ $institute->get('name') }}</span>
                        <span class="admin-brand-tag">Admin console</span>
                    </span>
                @endif
            </a>
            <button type="button" class="admin-sidebar-close lg:hidden" @click="sidebar = false" aria-label="Close navigation">
                <x-icon name="x" class="size-5" />
            </button>
        </div>

        <nav class="admin-sidebar-nav flex-1 space-y-4 overflow-y-auto px-3 pt-2 pb-4" aria-label="Sections">
            @foreach ($sections as $heading => $items)
                <div>
                    <p class="admin-section-label sidebar-text">{{ $heading }}</p>
                    <ul class="mt-1.5 space-y-1">
                        @foreach ($items as $item)
                            @php($active = request()->routeIs(...(array) $item['active']))
                            @php($badge = (int) ($item['badge'] ?? 0))
                            <li>
                                <a href="{{ route($item['route']) }}" class="admin-nav-link" title="{{ $item['label'] }}" aria-label="{{ $item['label'] }}{{ $badge > 0 ? ', '.$badge.' waiting' : '' }}" @if ($active) aria-current="page" @endif>
                                    <x-icon :name="$item['icon']" class="size-[1.125rem]" />
                                    <span class="sidebar-text flex-1 truncate">{{ $item['label'] }}</span>
                                    @if ($badge > 0)
                                        <span class="admin-badge sidebar-text" aria-hidden="true">{{ $badge > 99 ? '99+' : $badge }}</span>
                                        <span class="admin-badge-dot" aria-hidden="true"></span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-white/15 px-3 py-3">
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="admin-nav-link" title="View website" aria-label="View website (opens in a new tab)">
                        <x-icon name="external-link" class="size-[1.125rem]" />
                        <span class="sidebar-text flex-1">View website</span>
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" id="admin-logout-form">
                        @csrf
                        <button type="submit" class="admin-nav-link w-full" title="Log out" aria-label="Log out">
                            <x-icon name="log-out" class="size-[1.125rem]" />
                            <span class="sidebar-text">Log out</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </aside>

    <div class="admin-main" :inert="(sidebar && ! desktop) || $store.palette.open">
        {{-- Mobile top bar --}}
        <header class="sticky top-0 z-20 flex h-14 items-center gap-2 border-b border-[var(--admin-line)] bg-[var(--admin-canvas)]/90 px-3 backdrop-blur-md lg:hidden">
            <button type="button" x-ref="menuButton" class="rounded-lg p-2 text-slate-600 hover:bg-slate-200/60" @click="sidebar = true" aria-label="Open navigation" aria-controls="admin-sidebar" :aria-expanded="sidebar">
                <x-icon name="menu" class="size-5" />
            </button>
            <a href="{{ route('admin.dashboard') }}" class="min-w-0 truncate text-[0.95rem] font-semibold text-slate-900">{{ $institute->get('name') }}</a>
            <div class="ml-auto flex items-center gap-1">
                <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-200/60" @click="$store.palette.show()" aria-label="Search and commands"><x-icon name="search" class="size-5" /></button>
                <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-200/60" @click="$store.theme.toggle()" aria-label="Dark mode" :aria-pressed="$store.theme.dark">
                    <span x-show="! $store.theme.dark"><x-icon name="moon" class="size-5" /></span>
                    <span x-show="$store.theme.dark" x-cloak><x-icon name="sun" class="size-5" /></span>
                </button>
            </div>
        </header>

        <div class="admin-sheet">
            <div class="hidden h-16 items-center justify-between gap-4 border-b border-[var(--admin-line)] px-6 lg:flex xl:px-10">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900"
                            @click="$store.sidebar.toggle()" aria-label="Collapse sidebar" :aria-pressed="$store.sidebar.collapsed.toString()" :title="$store.sidebar.collapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                        <span x-show="! $store.sidebar.collapsed"><x-icon name="panel-left-close" class="size-[1.125rem]" /></span>
                        <span x-show="$store.sidebar.collapsed" x-cloak><x-icon name="panel-left-open" class="size-[1.125rem]" /></span>
                    </button>
                    <span class="h-5 w-px bg-slate-200" aria-hidden="true"></span>
                    <nav aria-label="Breadcrumb" class="min-w-0">
                        <ol class="flex items-center gap-2 text-sm">
                            <li><a href="{{ route('admin.dashboard') }}" class="text-[var(--admin-muted)] hover:text-slate-900">Admin</a></li>
                            <li aria-hidden="true" class="text-slate-300">/</li>
                            <li class="min-w-0"><span aria-current="page" class="block truncate font-medium text-slate-900">{{ $title }}</span></li>
                        </ol>
                    </nav>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" class="flex h-9 w-60 items-center gap-2.5 rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-[var(--admin-muted)] transition hover:border-slate-300 hover:text-slate-800 xl:w-72"
                            @click="$store.palette.show()" title="Search and commands">
                        <x-icon name="search" class="size-4" />
                        <span class="flex-1 text-left">Search or jump to…</span>
                        <span class="admin-kbd" x-text="$store.palette.shortcut">Ctrl K</span>
                    </button>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false"
                         @keydown.escape="if (open) { $event.stopPropagation(); open = false; $refs.newButton.focus(); }">
                        <button type="button" x-ref="newButton" class="btn-primary h-9" @click="open = ! open" :aria-expanded="open" aria-controls="admin-create-menu">
                            <x-icon name="plus" class="size-4" /> New <x-icon name="chevron-down" class="size-4 opacity-70" />
                        </button>
                        <div id="admin-create-menu" x-show="open" x-cloak x-transition.origin.top.right class="absolute top-full right-0 z-50 mt-1.5 w-64 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl">
                            <p class="px-2.5 pt-1 pb-1.5 text-[0.6875rem] font-semibold tracking-wider text-slate-500 uppercase">Create</p>
                            @foreach ($createActions as $action)
                                <a href="{{ $action['url'] }}" class="flex items-start gap-3 rounded-lg px-2.5 py-2 hover:bg-slate-50 focus-visible:bg-slate-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-navy-600">
                                    <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-md bg-navy-50 text-navy-700"><x-icon :name="$action['icon']" class="size-4" /></span>
                                    <span>
                                        <span class="block text-sm font-medium text-slate-900">{{ $action['label'] }}</span>
                                        <span class="block text-xs text-slate-500">{{ $action['hint'] }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900" @click="$store.theme.toggle()" aria-label="Dark mode" :aria-pressed="$store.theme.dark" title="Toggle dark mode">
                        <span x-show="! $store.theme.dark"><x-icon name="moon" class="size-[1.125rem]" /></span>
                        <span x-show="$store.theme.dark" x-cloak><x-icon name="sun" class="size-[1.125rem]" /></span>
                    </button>

                    <span class="h-5 w-px bg-slate-200" aria-hidden="true"></span>

                    <div class="flex items-center gap-2.5 pl-1" title="{{ $user->name }} · {{ $user->role->label() }}">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-navy-800 text-xs font-semibold text-white" aria-hidden="true">{{ $initials }}</span>
                        <span class="hidden leading-tight 2xl:block">
                            <span class="block text-sm font-medium text-slate-900">{{ $user->name }}</span>
                            <span class="block text-xs text-[var(--admin-muted)]">{{ $user->role->label() }}</span>
                        </span>
                        <span class="sr-only 2xl:hidden">Signed in as {{ $user->name }}</span>
                    </div>
                </div>
            </div>

            <main id="main" class="mx-auto max-w-[90rem] px-4 py-6 sm:px-6 lg:px-10 lg:py-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Command palette (Ctrl/⌘ + K or "/") --}}
    <div x-data="commandPalette(@js($paletteConfig))" @keydown.window="onGlobalKeydown($event)">
        <div x-show="$store.palette.open" x-cloak class="fixed inset-0 z-[60] flex items-start justify-center p-4 pt-[10vh]" role="dialog" aria-modal="true" aria-label="Search and commands"
             @keydown.escape.prevent.stop="$store.palette.hide()" @keydown.tab.prevent="$refs.input.focus()">
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" @click="$store.palette.hide()" x-show="$store.palette.open" x-transition.opacity></div>
            <div class="relative flex max-h-[75vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
                 x-show="$store.palette.open" x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="translate-y-2 scale-[.98] opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4">
                    <span class="text-slate-400"><x-icon name="search" class="size-5" /></span>
                    <label for="admin-palette-input" class="sr-only">Search students, courses, certificates or commands</label>
                    <input id="admin-palette-input" x-ref="input" type="text" x-model="query" @input.debounce.200ms="search()" @input="active = 0"
                           @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter.prevent="choose($event)"
                           role="combobox" :aria-expanded="(flat.length > 0).toString()" aria-controls="admin-palette-list" aria-autocomplete="list" :aria-activedescendant="flat.length ? 'palette-option-' + active : null"
                           placeholder="Search students, courses, certificates… or type a command" autocomplete="off" spellcheck="false"
                           class="h-14 min-w-0 flex-1 border-0 bg-transparent text-[0.95rem] text-slate-900 placeholder:text-slate-400 focus:ring-0 focus:outline-none">
                    <span class="admin-kbd">Esc</span>
                </div>

                <div class="flex-1 overflow-y-auto p-2">
                    <div id="admin-palette-list" role="listbox" aria-label="Results">
                        <template x-for="group in groups" :key="group.label">
                            <div role="group" :aria-label="group.label" class="pb-1">
                                <p class="px-3 pt-2.5 pb-1 text-[0.6875rem] font-semibold tracking-wider text-slate-500 uppercase" x-text="group.label" aria-hidden="true"></p>
                                <template x-for="item in group.items" :key="item.key">
                                    <div :id="'palette-option-' + item.index" role="option" :aria-selected="(item.index === active).toString()"
                                         @mousemove="active = item.index" @click="select(item, $event)"
                                         class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm" :class="item.index === active ? 'bg-slate-100 text-slate-900' : 'text-slate-700'">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500" x-html="icon(item.icon)"></span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-medium" x-text="item.title"></span>
                                            <span class="block truncate text-xs text-slate-500" x-show="item.subtitle" x-text="item.subtitle"></span>
                                        </span>
                                        <span class="text-slate-400" x-show="item.index === active" x-html="icon('corner-down-left')"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <p class="px-3 py-8 text-center text-sm text-slate-500" x-show="! flat.length && ! loading && ! failed" x-cloak>
                        No matches for “<span class="font-medium text-slate-700" x-text="query.trim()"></span>”.
                    </p>
                    <p class="px-3 pt-1 pb-3 text-xs text-red-700" x-show="failed" x-cloak>Record search is unavailable right now. Pages and commands still work.</p>
                    <p class="sr-only" aria-live="polite" x-text="announcement"></p>
                </div>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-slate-200 bg-slate-50 px-4 py-2.5 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="admin-kbd"><x-icon name="arrow-up" class="size-3" /></span><span class="admin-kbd"><x-icon name="arrow-down" class="size-3" /></span> move</span>
                    <span class="flex items-center gap-1.5"><span class="admin-kbd"><x-icon name="corner-down-left" class="size-3" /></span> open</span>
                    <span class="flex items-center gap-1.5"><span class="admin-kbd">/</span> or <span class="admin-kbd" x-text="$store.palette.shortcut">Ctrl K</span> anywhere</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
