<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Role;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman profil user.
     */
    public function profile(Request $request)
    {
        // Ambil data user dari session
        $user = [
            'name' => session('user_name', 'User'),
            'email' => session('user_email', 'user@example.com'),
        ];

        // Coba ambil record user di database (sinkronisasi SSO sebelumnya)
        $dbUser = null;
        $ssoId = session('user_sso_id');
        $email = session('user_email');
        if ($ssoId) {
            $dbUser = User::where('sso_id', $ssoId)->first();
        }
        if (!$dbUser && $email) {
            $dbUser = User::where('email', $email)->first();
        }

        return view('profile', ['user' => $user, 'dbUser' => $dbUser]);
    }
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_uuid' => 'required',
            'device_name' => 'required',
            'platform' => 'required',
            // 'app_id' tidak perlu divalidasi dari input user
        ]);

        $ssoBase = env('API_SSO_URL');
        if (empty($ssoBase)) {
            return back()->withErrors(['login' => 'SSO API URL belum dikonfigurasi. Silakan set API_SSO_URL di .env']);
        }

        $loginUrl = rtrim($ssoBase, '/') . '/api/login';

        $response = Http::post($loginUrl, [
            'email' => $request->email,
            'password' => $request->password,
            'device_uuid' => $request->device_uuid,
            'device_name' => $request->device_name,
            'platform' => $request->platform,
            'app_id' => env('APP_ID'),
        ]);

        if ($response->successful() && isset($response['access_token'])) {
            session(['access_token' => $response['access_token']]);
            session(['refresh_token' => $response['refresh_token'] ?? null]);
            // Simpan data user dari response SSO ke session jika tersedia
            if (isset($response['user'])) {
                $sso = $response['user'];

                session(['user_name' => $sso['name'] ?? 'User']);
                session(['user_email' => $sso['email'] ?? 'user@example.com']);

                // Sync to database (create or update)
                $ssoId = $sso['id'] ?? null;
                $email = $sso['email'] ?? null;

                $phone = $sso['phone'] ?? $sso['phone_number'] ?? $sso['no_telp'] ?? null;
                $gender = $sso['gender'] ?? null;
                $birthdate = $sso['birthdate'] ?? null;

                $user = null;
                if ($ssoId) {
                    $user = User::where('sso_id', $ssoId)->first();
                }
                if (!$user && $email) {
                    $user = User::where('email', $email)->first();
                }

                if (!$user) {
                    $user = new User();
                }

                $user->sso_id = $ssoId ?? $user->sso_id;
                $user->name = $sso['name'] ?? $user->name;
                $user->email = $sso['email'] ?? $user->email;
                $user->phone = $phone ?? $user->phone;
                $user->gender = $gender ?? $user->gender;
                if ($birthdate) {
                    // try to store as Y-m-d; leave raw if not parseable
                    $user->birthdate = $birthdate;
                }
                $user->sso_raw = $sso;

                // assign role if provided by SSO
                if (!empty($sso['role'])) {
                    $roleName = $sso['role'];
                    $role = Role::firstOrCreate(['name' => $roleName]);
                    $user->role_id = $role->id;
                    session(['user_role' => $roleName]);
                }

                $user->save();

                // If user has no role, assign default 'customer'
                if (empty($user->role_id)) {
                    $customerRole = Role::firstOrCreate(
                        ['name' => 'customer'],
                        ['display_name' => 'Customer']
                    );
                    $user->role_id = $customerRole->id;
                    $user->save();
                    session(['user_role' => $customerRole->name]);
                } else {
                    // ensure session reflects assigned role
                    if ($user->role) {
                        session(['user_role' => $user->role->name]);
                    }
                }

                // Store sso id in session (now that it's available)
                session(['user_sso_id' => $ssoId]);

                // Set some handy session values
                session(['user_gender' => $gender]);
                session(['user_birthdate' => $birthdate]);
                session(['user_phone' => $phone]);
            }
            return redirect('/');
        } else {
            $error = $response->json('message') ?? 'Login gagal!';
            return back()->withErrors(['login' => $error]);
        }
    }

    public function logout()
    {
        session()->forget(['access_token', 'refresh_token']);
        return redirect('/login');
    }
}