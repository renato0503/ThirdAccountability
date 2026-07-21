<x-guest-layout>
    <h5 class="fw-bold mb-1 text-center">Redefinir Senha</h5>
    <p class="text-muted text-center mb-4" style="font-size:.85rem">Crie uma nova senha para sua conta.</p>

    @if($errors->any())
    <div class="alert alert-danger py-2 mb-3" style="font-size:.875rem">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control form-control-lg" required autofocus autocomplete="username">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Nova Senha</label>
            <input id="password" type="password" name="password" class="form-control form-control-lg" required autocomplete="new-password">
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">Confirmar Senha</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control form-control-lg" required autocomplete="new-password">
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                <i class="bi bi-lock me-2"></i>Redefinir Senha
            </button>
        </div>
    </form>
</x-guest-layout>
