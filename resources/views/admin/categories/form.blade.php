<x-layouts.admin :title="$category->exists ? 'Edit category' : 'New category'">
    <x-page-header :title="$category->exists ? 'Edit category' : 'New category'" :back="route('admin.categories.index')" />

    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="card max-w-2xl space-y-5 p-6">
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <x-input name="name" label="Name" :value="$category->name" required />
        <x-input name="slug" label="Slug" :value="$category->slug" help="Leave blank to generate from the name. Used in catalogue URLs." />
        <x-textarea name="description" label="Description" :value="$category->description" rows="3" />
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">{{ $category->exists ? 'Save changes' : 'Create category' }}</button>
        </div>
    </form>
</x-layouts.admin>
