<x-guest-layout>
    @if(session('status'))
    <div class="alert alert-success py-2 mb-3" style="font-size:.875rem">{{ session('status') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger py-2 mb-3" style="font-size:.875rem">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <h5 class="fw-bold mb-1 text-center">Entrar no Sistema</h5>
    <p class="text-muted text-center mb-4" style="font-size:.85rem">Informe suas credenciais para acessar</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required autofocus autocomplete="username" placeholder="seu@email.com">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Senha</label>
            <input id="password" type="password" name="password" class="form-control form-control-lg" required autocomplete="current-password" placeholder="••••••••">
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                <label for="remember_me" class="form-check-label" style="font-size:.875rem">Lembrar de mim</label>
            </div>
            @if(Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size:.875rem">Esqueci a senha</a>
            @endif
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
            </button>
        </div>
    </form>
</x-guest-layout>
