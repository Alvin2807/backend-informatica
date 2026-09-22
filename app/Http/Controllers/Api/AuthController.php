<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function registrar(Request $request)
    {
        DB::beginTransaction();

        try {

        $datos = $request->validate([
            'cedula' => 'required|string|max:30|unique:inv_usuarios,cedula',
            'apellido' => 'required|string|max:100',
            'nombre' => 'required|string|max:100',
            'email' => 'nullable|email|max:255|unique:inv_usuarios,email',
            'usuario' => 'required|string|max:50|unique:inv_usuarios,usuario',
            'password' => 'required|string|min:8|confirmed',
            'fk_despacho' => 'required|integer|exists:inv_despachos,id_despacho',
        ]);

        $usuario = Usuario::create([
            'cedula' => $datos['cedula'],
            'apellido' => $datos['apellido'],
            'nombre' => $datos['nombre'],
            'email' => $datos['email'] ?? null,
            'usuario' => strtoupper($datos['usuario']),
            'password' => Hash::make($datos['password']),
            'estado' => 'Activo',
            'fk_despacho' => $datos['fk_despacho'],
        ]);

        // Confirmar la transacción
        DB::commit();

        // Crear token después del commit
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'ok' => true,
            'message' => 'Usuario registrado correctamente',
            'data' => $usuario,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where(
            'usuario',
            strtoupper($datos['usuario'])
        )->first();

        if (!$usuario || !Hash::check($datos['password'], $usuario->password)) {
            throw ValidationException::withMessages([
                'usuario' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if ($usuario->estado !== 'Activo') {
            return response()->json([
                'message' => 'El usuario se encuentra inactivo.',
            ], 403);
        }

        // Revoca tokens anteriores si quieres permitir
        // solamente una sesión activa.
        // $usuario->tokens()->delete();

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'usuario' => $usuario,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'usuario' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Todas las sesiones fueron cerradas correctamente',
        ]);
    }

        /**
     * Display the specified resource.
     */
    public function show(Usuario $usuario)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Usuario $usuario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usuario $usuario)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usuario $usuario)
    {
        //
    }
}
