<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
public function handle(Request $request, Closure $next, $role = null)
{
    if (Auth::check()) {
        if (Auth::user()->is_admin == 1) {
            return $next($request); // Allow admin to proceed
        }
        
        return redirect('/dashboard'); // Redirect non-admin users to a safe page
    }

    return redirect('/login'); // Redirect guests to login
}

}
