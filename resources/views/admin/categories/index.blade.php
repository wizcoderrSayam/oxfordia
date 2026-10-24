<x-layouts.admin title="Course categories">
    <x-page-header title="Course categories" subtitle="Group courses for the public catalogue filters.">
        <a href="{{ route('admin.categories.create') }}" class="btn-primary"><x-icon name="plus" /> New category</a>
    </x-page-header>

    <div class="card overflow-x-auto">
        @if ($categories->isEmpty())
            <x-empty-state icon="tags" title="No categories yet" message="Create categories such as Software Development or Design.">
                <a href="{{ route('admin.categories.create') }}" class="btn-primary">New category</a>
            </x-empty-state>
        @else
            <table class="table-base">
                <thead><tr><th>Name</th><th>Slug</th><th class="text-right">Courses</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $category->name }}</td>
                            <td class="font-mono text-xs">{{ $category->slug }}</td>
                            <td class="text-right">{{ $category->courses_count }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn-secondary btn-sm"><x-icon name="pencil" /> Edit</a>
                                    @if ($category->courses_count === 0)
                                        <x-confirm :action="route('admin.categories.destroy', $category)" method="DELETE" title="Delete category?" :message="'“'.$category->name.'” will be permanently deleted.'" confirm-label="Delete">
                                            <x-slot:trigger><x-icon name="trash-2" /> Delete</x-slot:trigger>
                                        </x-confirm>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $categories->links() }}</div>
</x-layouts.admin>
