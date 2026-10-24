<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PortalInvitation;
use App\Services\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Manages administrator and staff accounts. Student logins are managed from student records.
 */
class UserController extends Controller
{
    public function __construct(private ActivityLogger $logger) {}

    public function index(): View
    {
        $users = User::query()
            ->whereIn('role', [Role::Admin, Role::Staff])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in([Role::Admin->value, Role::Staff->value])],
        ]);

        $user = new User([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'password' => Str::password(32),
        ]);
        $user->role = Role::from($validated['role']);
        $user->is_active = true;
        $user->save();

        // Sent immediately so the password token is never written to the queue table.
        $user->notifyNow(new PortalInvitation(Password::broker()->createToken($user), backOffice: true));

        $this->logger->log('user.created', $user, "Created {$user->role->label()} account for {$user->email}");

        return redirect()->route('admin.users.index')->with('success', "Account created. A set-password email was sent to {$user->email}.");
    }

    public function edit(User $user): View
    {
        abort_if($user->isStudent(), 404);

        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isStudent(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in([Role::Admin->value, Role::Staff->value])],
            'is_active' => ['boolean'],
        ]);

        $role = Role::from($validated['role']);
        $isActive = $request->boolean('is_active');

        if ($user->is($request->user()) && ($role !== Role::Admin || ! $isActive)) {
            return back()->with('error', 'You cannot remove your own administrator access.');
        }

        $losingAdmin = $user->isAdmin() && $user->is_active && ($role !== Role::Admin || ! $isActive);

        $saved = DB::transaction(function () use ($user, $validated, $role, $isActive, $losingAdmin): bool {
            // Lock the active administrators so two concurrent demotions cannot leave none.
            $activeAdmins = User::query()->where('role', Role::Admin)->where('is_active', true)->lockForUpdate()->count();

            if ($losingAdmin && $activeAdmins <= 1) {
                return false;
            }

            $user->fill(['name' => $validated['name'], 'email' => Str::lower($validated['email'])]);
            $user->role = $role;
            $user->is_active = $isActive;
            $user->save();

            return true;
        });

        if (! $saved) {
            return back()->with('error', 'At least one active administrator is required.');
        }

        $changed = $this->logger->changedFields($user, ['updated_at', 'remember_token']);

        if ($changed) {
            $this->logger->log('user.updated', $user, "Updated account {$user->email}", ['fields' => $changed]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Account updated.');
    }
}
