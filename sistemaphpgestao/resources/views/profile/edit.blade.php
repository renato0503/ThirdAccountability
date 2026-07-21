@extends('layouts.app')


@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Meu Perfil</h4>
        <p class="text-muted mb-0" style="font-size:13px">Gerencie suas informações de conta</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">Informações do Perfil</div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Alterar Senha</div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card" style="border-color:#fca5a5;">
            <div class="card-header" style="color:#dc2626;">Zona de Perigo</div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
