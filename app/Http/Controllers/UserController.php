<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Método para verificar se o usuário é admin
     */
    private function checkAdmin()
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta área.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->checkAdmin();

        $users = User::latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->checkAdmin();

        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->checkAdmin();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'type' => 'required|in:admin,user,technical',
            'company_name' => 'nullable|string|max:255',
            'cnpj' => 'nullable|string|max:18|cnpj',
        ], [
            'name.required' => 'O campo nome é obrigatório.',
            'last_name.required' => 'O campo sobrenome é obrigatório.',
            'email.required' => 'O campo email é obrigatório.',
            'email.unique' => 'Este email já está em uso.',
            'password.required' => 'O campo senha é obrigatório.',
            'type.required' => 'O campo tipo de usuário é obrigatório.',
            'cnpj.cnpj' => 'CNPJ inválido.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'company_name' => $request->company_name,
            'cnpj' => $request->cnpj ? preg_replace('/[^0-9]/', '', $request->cnpj) : null,
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Qualquer usuário logado pode ver, mas apenas admins podem ver dados sensíveis
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->checkAdmin();

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->checkAdmin();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'type' => 'required|in:admin,user,technical',
            'company_name' => 'nullable|string|max:255',
            'cnpj' => 'nullable|string|max:18|cnpj',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'O campo nome é obrigatório.',
            'last_name.required' => 'O campo sobrenome é obrigatório.',
            'email.required' => 'O campo email é obrigatório.',
            'email.unique' => 'Este email já está em uso.',
            'type.required' => 'O campo tipo de usuário é obrigatório.',
            'cnpj.cnpj' => 'CNPJ inválido.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'type' => $request->type,
            'company_name' => $request->company_name,
            'cnpj' => $request->cnpj ? preg_replace('/[^0-9]/', '', $request->cnpj) : null,
            'is_active' => $request->is_active ?? $user->is_active,
            'permissions' => $request->permissions ?? $user->permissions,
        ];

        // Atualizar senha apenas se for fornecida
        if ($request->filled('password')) {
            $passwordValidator = Validator::make($request->all(), [
                'password' => ['confirmed', Rules\Password::defaults()],
            ], [
                'password.confirmed' => 'A confirmação da senha não corresponde.',
            ]);

            if ($passwordValidator->fails()) {
                return redirect()->back()
                    ->withErrors($passwordValidator)
                    ->withInput();
            }

            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->checkAdmin();

        // Validações para deletar
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Você não pode deletar sua própria conta!');
        }

        // Não permitir deletar o último admin
        $adminCount = User::where('type', 'admin')->count();
        if ($user->isAdmin() && $adminCount <= 1) {
            return redirect()->route('users.index')
                ->with('error', 'Não é possível deletar o único administrador do sistema!');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário deletado com sucesso!');
    }

    /**
     * Ativar/Desativar usuário
     */
    public function toggleStatus(User $user)
    {
        $this->checkAdmin();

        // Não permitir desativar a própria conta
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Você não pode desativar sua própria conta!');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'ativado' : 'desativado';

        return redirect()->route('users.index')
            ->with('success', "Usuário {$status} com sucesso!");
    }
}
