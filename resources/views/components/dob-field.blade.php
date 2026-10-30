{{-- Day / month / year selects for public lookups that confirm identity with a date of birth. --}}
<fieldset class="dob-field">
    <legend class="form-label">Date of birth <span class="text-red-500" aria-hidden="true">*</span></legend>
    <div class="dob-field__selects">
        <label class="sr-only" for="dob_day">Day</label>
        <select id="dob_day" name="dob_day" class="form-input" required>
            <option value="">Day</option>
            @foreach (range(1, 31) as $day)
                <option value="{{ $day }}" @selected((int) old('dob_day', request('dob_day')) === $day)>{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="dob_month">Month</label>
        <select id="dob_month" name="dob_month" class="form-input" required>
            <option value="">Month</option>
            @foreach (range(1, 12) as $month)
                <option value="{{ $month }}" @selected((int) old('dob_month', request('dob_month')) === $month)>{{ \Illuminate\Support\Carbon::create(2000, $month, 1)->format('F') }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="dob_year">Year</label>
        <select id="dob_year" name="dob_year" class="form-input" required>
            <option value="">Year</option>
            @foreach (range(now()->year, 1950) as $year)
                <option value="{{ $year }}" @selected((int) old('dob_year', request('dob_year')) === $year)>{{ $year }}</option>
            @endforeach
        </select>
    </div>
    @foreach (['date_of_birth', 'dob_day', 'dob_month', 'dob_year'] as $dobField)
        @error($dobField) <p class="form-error">{{ $message }}</p> @break @enderror
    @endforeach
</fieldset>
