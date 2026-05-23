<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate user and issue Sanctum token
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Validate password against hashed field
        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            return response()->json([
                'message' => 'Credenciales de acceso incorrectas.'
            ], 401);
        }

        // Generate Sanctum dynamic API token
        $token = $user->createToken('decorarte-auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Retrieve authenticated user details and permissions
     */
    public function me()
    {
        return response()->json(Auth::user());
    }

    /**
     * Destroy current session tokens
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada y tokens revocados correctamente.'
        ]);
    }

    /**
     * Register a new user under default roles
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|required|in:admin,supervisor,editor,instructor,empleado,alumno,visualizador'
        ]);

        // Default permissions matrix depending on roles
        $permissions = [];
        $role = $validated['role'] ?? 'alumno';
        
        switch ($role) {
            case 'admin':
                $permissions = ['all'];
                break;
            case 'supervisor':
                $permissions = ['tasks.manage', 'posts.approve', 'courses.view'];
                break;
            case 'editor':
                $permissions = ['posts.create', 'video.edit', 'prompts.manage'];
                break;
            case 'instructor':
                $permissions = ['courses.manage', 'courses.grade'];
                break;
            case 'empleado':
                $permissions = ['tasks.view', 'tasks.complete', 'courses.view'];
                break;
            default:
                $permissions = ['courses.view', 'courses.take'];
                break;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $role,
            'permissions' => $permissions,
            'avatar_url' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150', // default avatar
            'schedule_config' => []
        ]);

        $token = $user->createToken('decorarte-auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 201);
    }

    /**
     * Setup 2FA configuration secret key (Simulated)
     */
    public function enable2FA(Request $request)
    {
        $user = Auth::user();
        
        // Generate simulated Google Authenticator secret
        $secret = 'KVKXT2O2ZRDVMV3T'; // 16 digit base32 string
        
        // Save in metadata/config
        $user->schedule_config = array_merge($user->schedule_config ?? [], [
            'two_factor_secret' => $secret,
            'two_factor_enabled' => false
        ]);
        $user->save();

        return response()->json([
            'secret' => $secret,
            'qr_code_url' => 'otpauth://totp/DecorArte:' . $user->email . '?secret=' . $secret . '&issuer=DecorArte'
        ]);
    }

    /**
     * Complete 2FA enrollment verification (Simulated)
     */
    public function verify2FA(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = User::find(Auth::id());
        $config = $user->schedule_config ?? [];
        
        // Simple verification simulation
        if ($request->input('code') === '123456' || isset($config['two_factor_secret'])) {
            $config['two_factor_enabled'] = true;
            $user->schedule_config = $config;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Doble Factor de Autenticación (2FA) configurado exitosamente.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Código OTP ingresado incorrecto.'
        ], 422);
    }
}
