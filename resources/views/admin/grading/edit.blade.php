@php
    $rows = array_values(old('grade_bands', $bands));
@endphp
<x-layouts.admin title="Grading">
    <x-page-header title="Grading" subtitle="Set the minimum percentage needed for each grade." />

    <form method="POST" action="{{ route('admin.grading.update') }}" class="card max-w-2xl overflow-hidden"
          x-data="{
              bands: @js($rows),
              submitting: false,
              sorted() { return this.bands.map((band, index) => ({ ...band, index })).filter((band) => band.min !== '' && ! isNaN(band.min)).sort((a, b) => b.min - a.min); },
              range(band) {
                  const list = this.sorted();
                  const position = list.findIndex((item) => item.index === band.index);
                  if (position === -1) return '—';
                  if (position === 0) return Number(band.min) + '% and above';
                  return Number(band.min) + '% to below ' + Number(list[position - 1].min) + '%';
              },
              lowest() { const list = this.sorted(); return list.length ? Number(list[list.length - 1].min) : null; },
          }"
          @submit="submitting = true">
        @csrf @method('PUT')

        @if ($errors->any())
            <x-callout tone="danger" class="mx-6 mt-4">{{ $errors->first() }}</x-callout>
        @endif

        <table class="table-base">
            <thead><tr><th>Grade</th><th>Minimum %</th><th>Percentage range</th><th><span class="sr-only">Remove</span></th></tr></thead>
            <tbody>
                <template x-for="(band, index) in bands" :key="index">
                    <tr>
                        <td class="w-36">
                            <label class="sr-only" :for="'band-grade-' + index">Grade</label>
                            <input :id="'band-grade-' + index" :name="'grade_bands[' + index + '][grade]'" x-model="band.grade" class="form-input" placeholder="e.g. A" required maxlength="20">
                        </td>
                        <td class="w-36">
                            <label class="sr-only" :for="'band-min-' + index">Minimum percentage</label>
                            <input :id="'band-min-' + index" :name="'grade_bands[' + index + '][min]'" x-model="band.min" type="number" step="0.01" min="0" max="100" class="form-input" placeholder="e.g. 90" required>
                        </td>
                        <td class="text-sm text-slate-600" x-text="range({ ...band, index })"></td>
                        <td class="w-12 text-right">
                            <button type="button" class="btn-secondary btn-sm" @click="bands.splice(index, 1)" x-show="bands.length > 1" aria-label="Remove grade"><x-icon name="trash-2" /></button>
                        </td>
                    </tr>
                </template>
                <tr class="bg-slate-50">
                    <td class="font-semibold text-red-700">Fail</td>
                    <td class="text-slate-400">—</td>
                    <td class="text-sm text-slate-600" x-text="lowest() === null ? '—' : 'Below ' + lowest() + '%, or below the min marks in any subject'"></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-6 py-4">
            <button type="button" class="btn-secondary btn-sm" @click="bands.push({ grade: '', min: '' })" x-show="bands.length < 10"><x-icon name="plus" /> Add grade</button>
            <button type="submit" class="btn-primary" :disabled="submitting">Save grades</button>
        </div>
        <p class="border-t border-slate-100 px-6 py-3 text-xs text-slate-500">Saving updates the grades of existing results too. Results that already have a certificate are not changed.</p>
    </form>
</x-layouts.admin>
