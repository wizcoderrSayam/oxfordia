<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->staff()->create());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Ravi Shankar',
            'father_name' => 'Mohan Shankar',
            'mother_name' => 'Sita Shankar',
            'date_of_birth' => '1990-03-14',
            'address' => '12 MG Road, Lucknow',
            'position' => 'Senior Faculty',
            'joining_date' => '2021-07-01',
            'status' => 'active',
            'bio' => 'Teaches programming fundamentals.',
        ], $overrides);
    }

    public function test_registering_employees_generates_sequential_registration_numbers(): void
    {
        $this->post(route('admin.employees.store'), $this->payload())->assertRedirect();
        $this->post(route('admin.employees.store'), $this->payload(['full_name' => 'Second Employee']))->assertRedirect();

        $year = now()->year;

        $this->assertSame(["EMP-{$year}-0001", "EMP-{$year}-0002"], Employee::query()->orderBy('id')->pluck('registration_number')->all());
    }

    public function test_registration_number_cannot_be_set_through_the_form(): void
    {
        $this->post(route('admin.employees.store'), $this->payload(['registration_number' => 'HACKED-1']));

        $this->assertDatabaseMissing('employees', ['registration_number' => 'HACKED-1']);
    }

    public function test_an_employee_photo_is_stored_and_replaced(): void
    {
        $this->post(route('admin.employees.store'), $this->payload(['photo' => UploadedFile::fake()->image('first.jpg')]));

        $employee = Employee::query()->sole();
        $firstPhoto = $employee->profile_photo_path;
        Storage::disk('public')->assertExists($firstPhoto);

        $this->put(route('admin.employees.update', $employee), $this->payload(['photo' => UploadedFile::fake()->image('second.jpg')]))
            ->assertRedirect(route('admin.employees.show', $employee));

        Storage::disk('public')->assertMissing($firstPhoto);
        Storage::disk('public')->assertExists($employee->fresh()->profile_photo_path);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->post(route('admin.employees.store'), $this->payload(['full_name' => '', 'date_of_birth' => '', 'position' => '', 'status' => 'retired']))
            ->assertSessionHasErrors(['full_name', 'date_of_birth', 'position', 'status']);
    }

    public function test_the_admin_screens_render(): void
    {
        $employee = Employee::factory()->create(['full_name' => 'Ravi Shankar']);

        $this->get(route('admin.employees.index'))->assertOk()->assertSee('Ravi Shankar');
        $this->get(route('admin.employees.create'))->assertOk();
        $this->get(route('admin.employees.show', $employee))->assertOk()->assertSee($employee->registration_number);
        $this->get(route('admin.employees.edit', $employee))->assertOk();
    }

    public function test_the_employee_list_has_edit_and_delete_actions(): void
    {
        $employee = Employee::factory()->create();

        $this->get(route('admin.employees.index'))
            ->assertOk()
            ->assertSee(route('admin.employees.edit', $employee), false)
            ->assertSee('action="'.route('admin.employees.destroy', $employee).'"', false)
            ->assertSee('Delete employee?');
    }

    public function test_saving_an_edited_employee_asks_for_confirmation(): void
    {
        $employee = Employee::factory()->create();

        $this->get(route('admin.employees.edit', $employee))
            ->assertOk()
            ->assertSee('form="employee-form"', false)
            ->assertSee('Save changes?');

        $this->get(route('admin.employees.create'))
            ->assertOk()
            ->assertDontSee('Save changes?');
    }

    public function test_an_employee_can_be_deleted(): void
    {
        $employee = Employee::factory()->create();

        $this->delete(route('admin.employees.destroy', $employee))->assertRedirect(route('admin.employees.index'));

        $this->assertModelMissing($employee);
    }

    public function test_students_cannot_manage_employees(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.employees.index'))->assertForbidden();
    }

    public function test_the_employee_list_shows_the_date_of_birth(): void
    {
        Employee::factory()->create(['date_of_birth' => '1990-03-09']);

        $this->get(route('admin.employees.index'))
            ->assertOk()
            ->assertSee('Date of birth')
            ->assertSee('09 Mar 1990');
    }
}
