<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
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
    /**
     * Tampilkan form edit profil.
     */
    public function editProfile(Request $request)
    {
        $ssoId = session('user_sso_id');
        $email = session('user_email');

        $dbUser = null;
        if ($ssoId) {
            $dbUser = User::where('sso_id', $ssoId)->first();
        }
        if (!$dbUser && $email) {
            $dbUser = User::where('email', $email)->first();
        }

        $user = [
            'name' => session('user_name', 'User'),
            'email' => session('user_email', 'user@example.com'),
        ];

        return view('update-profile', ['user' => $user, 'dbUser' => $dbUser]);
    }

    /**
     * Proses update profil: update ke SSO dan sinkron ke DB + session.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'phone' => 'nullable|string|max:40',
        ]);

        // Normalize gender value to lowercase to accept case variations (e.g. 'Male', 'male')
        $genderRaw = $request->input('gender');
        $gender = $genderRaw ? strtolower($genderRaw) : null;
        if ($gender && !in_array($gender, ['male', 'female', 'other'])) {
            return back()->withErrors(['gender' => 'Gender tidak valid.']);
        }

        // Map local gender values to SSO expected values (SSO expects 'pria' or 'wanita')
        $ssoGender = null;
        if ($gender === 'male') {
            $ssoGender = 'pria';
        } elseif ($gender === 'female') {
            $ssoGender = 'wanita';
        } else {
            // other -> do not send gender to SSO (SSO doesn't support 'other')
            $ssoGender = null;
        }

        // Normalize and trim phone to SSO limits (max 20)
        $phoneRaw = $request->input('phone');
        $phone = $phoneRaw ? substr($phoneRaw, 0, 20) : null;

        $payload = [
            'name' => $request->input('name'),
            'gender' => $ssoGender,
            'birthdate' => $request->input('birthdate'),
            'phone' => $phone,
            // include email as read-only identifier if available
            'email' => session('user_email'),
            // include sso identifier if available (some SSO servers require this)
            'sso_id' => session('user_sso_id'),
            'app_id' => env('APP_ID'),
        ];
        // also include alternative phone keys some SSO implementations expect
        if ($phone) {
            $payload['phone_number'] = $phone;
            $payload['no_telp'] = $phone;
        }

        $ssoBase = env('API_SSO_URL');
        if (empty($ssoBase)) {
            return back()->withErrors(['update' => 'SSO API URL belum dikonfigurasi.']);
        }

        $updateUrl = rtrim($ssoBase, '/') . '/api/update-profile';

        try {
            $http = Http::withOptions(['connect_timeout' => 5, 'timeout' => 10])->accept('application/json');
            if (session('access_token')) {
                $http = $http->withToken(session('access_token'));
            }

            // Log request payload (sanitized) to help debugging
            Log::info('SSO update request', ['url' => $updateUrl, 'payload_keys' => array_keys($payload)]);

            $response = $http->post($updateUrl, $payload);

            // Log response for diagnosis
            Log::info('SSO update response', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (ConnectionException $e) {
            Log::error('SSO update connection failed: '.$e->getMessage(), ['url' => $updateUrl]);
            return back()->withErrors(['update' => 'Tidak dapat terhubung ke layanan SSO. Silakan coba lagi nanti.']);
        } catch (\Exception $e) {
            Log::error('SSO update request error: '.$e->getMessage(), ['url' => $updateUrl]);
            return back()->withErrors(['update' => 'Terjadi kesalahan saat menghubungi SSO.']);
        }

        if ($response->successful()) {
            // Update local DB user if exists
            $ssoId = session('user_sso_id');
            $email = session('user_email');
            $userModel = null;
            if ($ssoId) {
                $userModel = User::where('sso_id', $ssoId)->first();
            }
            if (!$userModel && $email) {
                $userModel = User::where('email', $email)->first();
            }

            if (!$userModel) {
                $userModel = new User();
                $userModel->email = $email;
                $userModel->sso_id = $ssoId;
            }

            $userModel->name = $payload['name'] ?? $userModel->name;
            $userModel->gender = $payload['gender'] ?? $userModel->gender;
            $userModel->birthdate = $payload['birthdate'] ?? $userModel->birthdate;
            $userModel->phone = $payload['phone'] ?? $userModel->phone;
            $userModel->save();

            // Update session values
            session(['user_name' => $userModel->name]);
            session(['user_gender' => $userModel->gender]);
            session(['user_birthdate' => $userModel->birthdate]);
            session(['user_phone' => $userModel->phone]);

            return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
        }

        $status = $response->status();
        $body = $response->body();
        Log::warning('SSO update failed', ['status' => $status, 'body' => $body, 'url' => $updateUrl]);

        $errorMsg = $response->json('message') ?? 'Gagal memperbarui profil di SSO.';
        return back()->withErrors(['update' => $errorMsg]);
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

        try {
            $response = Http::withOptions([
                // connection timeout (seconds) and overall timeout
                'connect_timeout' => 5,
                'timeout' => 10,
            ])->post($loginUrl, [
                'email' => $request->email,
                'password' => $request->password,
                'device_uuid' => $request->device_uuid,
                'device_name' => $request->device_name,
                'platform' => $request->platform,
                'app_id' => env('APP_ID'),
            ]);
        } catch (ConnectionException $e) {
            Log::error('SSO connection failed: '.$e->getMessage(), ['url' => $loginUrl]);
            return back()->withErrors(['login' => 'Tidak dapat terhubung ke layanan SSO. Silakan coba lagi beberapa saat.']);
        } catch (\Exception $e) {
            Log::error('SSO request error: '.$e->getMessage(), ['url' => $loginUrl]);
            return back()->withErrors(['login' => 'Terjadi kesalahan saat menghubungi SSO. Silakan coba lagi.']);
        }

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
            $status = $response->status();
            $body = $response->body();
            Log::warning('SSO login failed', ['status' => $status, 'body' => $body, 'url' => $loginUrl]);

            if (config('app.debug')) {
                $msg = $response->json('message') ?? $body;
                $error = "SSO error: {$status} - {$msg}";
            } else {
                $error = $response->json('message') ?? 'Login gagal!';
            }

            return back()->withErrors(['login' => $error]);
        }
    }

    public function logout()
    {
        session()->forget(['access_token', 'refresh_token']);
        return redirect('/login');
    }

    /**
     * Show change password form.
     */
    public function showResetPasswordForm()
    {
        return view('reset-password');
    }

    /**
     * Handle password change request by forwarding to SSO API.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            // enforce SSO rules: min 8, 1 lower, 1 upper, 1 symbol
            'new_password' => ['required', 'string', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/'],
        ], [
            'new_password.regex' => 'Minimal 8 karakter, 1 huruf kecil, 1 huruf kapital, dan 1 simbol.',
        ]);

        $ssoBase = env('API_SSO_URL');
        if (empty($ssoBase)) {
            return back()->withErrors(['password' => 'SSO API URL belum dikonfigurasi.']);
        }

        $resetUrl = rtrim($ssoBase, '/') . '/api/reset-password';

        $payload = [
            'current_password' => $request->input('current_password'),
            'new_password' => $request->input('new_password'),
            'new_password_confirmation' => $request->input('new_password_confirmation'),
            'app_id' => env('APP_ID'),
        ];

        try {
            $http = Http::withOptions(['connect_timeout' => 5, 'timeout' => 10])->accept('application/json');
            if (session('access_token')) {
                $http = $http->withToken(session('access_token'));
            }

            Log::info('SSO reset-password request', ['url' => $resetUrl, 'payload_keys' => array_keys($payload)]);

            $response = $http->post($resetUrl, $payload);

            Log::info('SSO reset-password response', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (ConnectionException $e) {
            Log::error('SSO reset-password connection failed: '.$e->getMessage(), ['url' => $resetUrl]);
            return back()->withErrors(['password' => 'Tidak dapat terhubung ke layanan SSO. Silakan coba lagi nanti.']);
        } catch (\Exception $e) {
            Log::error('SSO reset-password request error: '.$e->getMessage(), ['url' => $resetUrl]);
            return back()->withErrors(['password' => 'Terjadi kesalahan saat menghubungi SSO.']);
        }

        if ($response->successful()) {
            return redirect()->route('profile')->with('success', 'Password berhasil diubah.');
        }

        $status = $response->status();
        $body = $response->body();
        Log::warning('SSO reset-password failed', ['status' => $status, 'body' => $body, 'url' => $resetUrl]);

        $errorMsg = $response->json('message') ?? 'Gagal mengubah password.';
        return back()->withErrors(['password' => $errorMsg]);
    }
}