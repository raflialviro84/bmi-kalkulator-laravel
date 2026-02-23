<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class EnsureAdmin
{
    public function handle($request, Closure $next)
    {
        // Fast check via session
        if (session('user_role') === 'admin') {
            return $next($request);
        }

        // Fallback: try to resolve from database using sso_id or email in session
        $ssoId = session('user_sso_id');
        $email = session('user_email');

        $user = null;
        if ($ssoId) {
            $user = User::where('sso_id', $ssoId)->first();
        }
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            // ensure user has a role; if not, assign default 'customer'
            if (!$user->role_id) {
                $customer = \App\Models\Role::firstOrCreate(
                    ['name' => 'customer'],
                    ['display_name' => 'Customer']
                );
                $user->role_id = $customer->id;
                $user->save();
                session(['user_role' => $customer->name]);
            } else {
                session(['user_role' => $user->role->name ?? 'customer']);
            }

            if ($user->role && $user->role->name === 'admin') {
                return $next($request);
            }
        }

        abort(403, 'Forbidden');
    }
}
