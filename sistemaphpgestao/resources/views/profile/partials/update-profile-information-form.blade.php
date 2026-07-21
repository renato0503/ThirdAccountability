<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    @if(session('status') === 'profile-updated')
    <div class="alert alert-success py-2 mb-3" style="font-size:.875rem">Perfil atualizado com sucesso.</div>
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required autofocus autocomplete="name">
        @error('name')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
    </div>

    <div class="mb-4">
        <label for="email" class="form-label">E-mail</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required autocomplete="username">
        @error('email')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="alert alert-warning py-2 mt-2" style="font-size:.875rem">
            Seu e-mail não foi verificado.
            <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-inline">@csrf</form>
            <button form="send-verification" class="btn btn-link p-0" style="font-size:.875rem">Reenviar link de verificação.</button>
        </div>
        @if(session('status') === 'verification-link-sent')
        <div class="alert alert-success py-2 mt-1" style="font-size:.875rem">Novo link enviado.</div>
        @endif
        @endif
    </div>

    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
</form>
