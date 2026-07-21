<x-guest-layout>
    @if($errors->any())
    <div class="alert alert-danger py-2 mb-3" style="font-size:.875rem">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <h5 class="fw-bold mb-1 text-center">Criar Conta</h5>
    <p class="text-muted text-center mb-4" style="font-size:.85rem">Preencha os dados para cadastro</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Nome Completo</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control form-control-lg" required autofocus autocomplete="name">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required autocomplete="username">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Senha</label>
            <input id="password" type="password" name="password" class="form-control form-control-lg" required autocomplete="new-password">
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">Confirmar Senha</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control form-control-lg" required autocomplete="new-password">
        </div>
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                <i class="bi bi-person-plus me-2"></i>Cadastrar
            </button>
        </div>
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none" style="font-size:.875rem">Já tem conta? Entrar</a>
        </div>
    </form>
</x-guest-layout>
