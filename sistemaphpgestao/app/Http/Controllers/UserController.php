<?php
namespace App\Http\Controllers;

use App\Models\{User, Institution, AuditLog};
use App\Mail\BoasVindasMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Mail};

class UserController extends Controller {
    public function index() {
        $user = Auth::user();
        if (!in_array($user->role,['ADMIN_GERAL','ADMIN_INSTITUICAO'])) abort(403);
        $q = User::with('institution');
        if ($user->isInstAdmin()) $q->where('institution_id',$user->institution_id);
        $users = $q->orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create() {
        $user = Auth::user();
        if (!in_array($user->role,['ADMIN_GERAL','ADMIN_INSTITUICAO'])) abort(403);
        $institutions = $user->isAdmin() ? Institution::where('active',true)->orderBy('razao_social')->get() : Institution::where('id',$user->institution_id)->get();
        $roles = array_keys(User::ROLES);
        return view('users.create', compact('institutions','roles'));
    }

    public function store(Request $req) {
        $user = Auth::user();
        if (!in_array($user->role,['ADMIN_GERAL','ADMIN_INSTITUICAO'])) abort(403);
        $allRoles = array_keys(User::ROLES);
        $allowedRoles = $user->isAdmin()
            ? $allRoles
            : array_values(array_diff($allRoles, ['ADMIN_GERAL']));
        $data = $req->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6|confirmed',
            'role'           => ['required','string','in:'.implode(',',$allowedRoles)],
            'institution_id' => 'nullable|exists:institutions,id',
        ]);
        if ($user->isInstAdmin()) {
            $data['institution_id'] = $user->institution_id;
        }
        $senhaOriginal    = $data['password'];
        $data['password'] = Hash::make($data['password']);
        $newUser = User::create($data);
        AuditLog::create(['user_id'=>Auth::id(),'acao'=>'CREATE','entidade'=>'User','entidade_id'=>$newUser->id,'dados'=>json_encode(['name'=>$newUser->name,'email'=>$newUser->email,'role'=>$newUser->role]),'ip'=>request()->ip()]);
        Mail::to($newUser->email)->queue(new BoasVindasMail($newUser, $senhaOriginal));
        return redirect()->route('usuarios.index')->with('success','Usuário criado! E-mail de boas-vindas enviado.');
    }

    public function edit(User $usuario) {
        $user = Auth::user();
        if (!in_array($user->role,['ADMIN_GERAL','ADMIN_INSTITUICAO'])) abort(403);
        $institutions = $user->isAdmin() ? Institution::where('active',true)->orderBy('razao_social')->get() : Institution::where('id',$user->institution_id)->get();
        $roles = array_keys(User::ROLES);
        return view('users.edit', compact('usuario','institutions','roles'));
    }

    public function update(Request $req, User $usuario) {
        $user = Auth::user();
        if (!in_array($user->role,['ADMIN_GERAL','ADMIN_INSTITUICAO'])) abort(403);
        if ($usuario->email === 'admin@gestao.org') {
            return back()->with('error','Este usuário é protegido e não pode ser alterado.');
        }
        $allRoles = array_keys(User::ROLES);
        $allowedRoles = $user->isAdmin()
            ? $allRoles
            : array_values(array_diff($allRoles, ['ADMIN_GERAL']));
        $data = $req->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,'.$usuario->id,
            'role'           => ['required','string','in:'.implode(',',$allowedRoles)],
            'institution_id' => 'nullable|exists:institutions,id',
            'active'         => 'boolean',
        ]);
        if ($user->isInstAdmin()) {
            $data['institution_id'] = $user->institution_id;
        }
        if ($req->filled('password')) {
            $req->validate(['password'=>'string|min:6|confirmed']);
            $data['password'] = Hash::make($req->password);
        }
        $data['active'] = $req->boolean('active', true);
        $usuario->update($data);
        return redirect()->route('usuarios.index')->with('success','Usuário atualizado!');
    }

    public function destroy(User $usuario) {
        if (!Auth::user()->isAdmin()) abort(403);
        if ($usuario->email === 'admin@gestao.org') {
            return back()->with('error','Este usuário é protegido e não pode ser desativado.');
        }
        $usuario->update(['active'=>false]);
        return back()->with('success','Usuário desativado.');
    }
}
