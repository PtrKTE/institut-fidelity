@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-user-shield me-2"></i>Utilisateurs</h4>
    <a href="{{ route('utilisateurs.create') }}" class="btn-fid-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Nouvel utilisateur
    </a>
</div>

<div class="fid-card">
    <div class="fid-table-wrapper">
        <table class="fid-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Lieu</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><strong>{{ $user->nom }}</strong></td>
                        <td>{{ $user->prenom }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="fid-badge fid-badge-info">{{ ucfirst($user->role) }}</span></td>
                        <td>{{ $user->lieu_affecte ?: '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('utilisateurs.edit', $user) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('utilisateurs.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4"><div class="fid-empty"><p>Aucun utilisateur</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
