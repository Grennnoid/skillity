<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function teachEntry()
    {
        if (!Auth::check()) {
            return redirect()->route('login', ['intent' => 'teach']);
        }

        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'dosen') {
            return redirect()->route('dosen.dashboard');
        }

        return view('auth.teach-on-skillify', [
            'isPending' => $this->isPendingDosenRequest($user),
        ]);
    }

    public function showDosenPendingApproval()
    {
        $user = Auth::user();
        abort_unless($user && $this->isPendingDosenRequest($user), 403);

        return view('auth.dosen-pending-approval');
    }

    public function showLogin()
    {
        if (Auth::check() && !request()->boolean('switch')) {
            return $this->redirectToDashboard(Auth::user()->role);
        }

        return view('auth.login', [
            'loginIntent' => request()->query('intent'),
        ]);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user()->role);
        }

        return view('auth.register');
    }

    public function showProfile()
    {
        $user = Auth::user();
        $favoriteCourses = $user
            ->courseStates()
            ->whereRaw('"is_favorite" IS TRUE')
            ->orderBy('course_title')
            ->get(['course_slug', 'course_title']);

        $dosenCourses = collect();
        if ($user->role === 'dosen') {
            $dosenCourses = DB::table('quizzes')
                ->where('created_by', $user->id)
                ->orderByDesc('created_at')
                ->get(['id', 'title']);
        }

        return view('profile', compact('favoriteCourses', 'dosenCourses'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile berhasil diperbarui.');
    }

    public function destroyAccount(Request $request)
    {
        $request->validate([
            'current_password_delete' => ['required'],
        ], [
            'current_password_delete.required' => 'Masukkan password untuk hapus akun.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password_delete, $user->password)) {
            return back()->withErrors([
                'current_password_delete' => 'Password tidak sesuai. Akun tidak dihapus.',
            ]);
        }

        if (!empty($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')->with('success', 'Akun berhasil dihapus.');
    }

    public function uploadProfileImage(Request $request)
    {
        $validated = $request->validate([
            'profile_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $user = Auth::user();

        if (!empty($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $validated['profile_image']->store('profile_pics', 'public');

        $user->update([
            'profile_image' => $path,
        ]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'requested_role' => null,
            'dosen_request_status' => 'none',
            'account_status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('student.pathfinder', ['welcome' => 1]);
    }

    public function requestDosenAccess(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'dosen') {
            return redirect()->route('dosen.dashboard');
        }

        if ($this->isPendingDosenRequest($user)) {
            return redirect()->route('teach.entry')
                ->with('success', 'Pengajuan dosen kamu masih diproses admin.');
        }

        $user->update([
            'requested_role' => 'dosen',
            'dosen_request_status' => 'pending',
        ]);

        return redirect()->route('teach.entry')
            ->with('success', 'Pengajuan jadi dosen berhasil dikirim. Tunggu approval dari admin.');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'login_as' => ['required', 'in:student,dosen'],
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->account_status === 'suspended') {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Account kamu sedang di-suspend. Hubungi admin.',
                ])->onlyInput('email');
            }

            $desiredRole = $credentials['login_as'];

            if ($request->input('intent') === 'teach' || $desiredRole === 'dosen') {
                if ($user->role === 'student') {
                    return redirect()->route('teach.entry')
                        ->with('success', 'Akun kamu sudah masuk. Sekarang kamu bisa lanjut ajukan akses dosen.');
                }

                return $this->redirectToDashboard($user->role);
            }

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            if ($desiredRole === 'student') {
                if ($user->role === 'dosen') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors([
                        'email' => 'Akun dosen tidak punya akses ke dashboard student. Pilih login sebagai dosen.',
                    ])->onlyInput('email');
                }

                return redirect()->intended(route('student.dashboard'));
            }

            if ($user->role === 'dosen') {
                return redirect()->intended(route('dosen.dashboard'));
            }

            if ($user->role === 'student') {
                return redirect()->intended(route('student.dashboard'));
            }

            return redirect()->route('landing');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah!',
        ])->onlyInput('email');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password lama jangan dikosongin ya.',
            'new_password.min' => 'Password baru minimal 8 karakter biar aman.',
            'new_password.confirmed' => 'Konfirmasi passwordnya belum cocok.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama kamu salah.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password kamu sudah berhasil diganti.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    private function redirectToDashboard(string $role)
    {
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'dosen') {
            return redirect()->route('dosen.dashboard');
        }

        return redirect()->route('student.dashboard');
    }

    private function isPendingDosenRequest(User $user): bool
    {
        return $user->requested_role === 'dosen' && $user->dosen_request_status === 'pending';
    }
}
