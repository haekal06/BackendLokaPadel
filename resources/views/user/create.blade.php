@extends('index')

@section('main-content')
@can('admin') <!-- Hanya admin yang bisa mengakses halaman ini -->
<h1 class="h3 mb-4 text-gray-800">{{ $title ?? __('Create User') }}</h1>

<div class="card">
    <div class="card-body">
        <form action="{{ route('user.store') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    name="name"
                    id="name"
                    placeholder="First name"
                    value="{{ old('name') }}">
                @error('name')
                <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text"
                    class="form-control @error('last_name') is-invalid @enderror"
                    name="last_name"
                    id="last_name"
                    placeholder="Last name"
                    value="{{ old('last_name') }}">
                @error('last_name')
                <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    name="email"
                    id="email"
                    placeholder="Email"
                    value="{{ old('email') }}">
                @error('email')
                <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    name="password"
                    id="password"
                    placeholder="Password">
                @error('password')
                <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="Confirm Password">
                @error('password_confirmation')
                <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select class="form-control" name="role" id="role" required>
                    <option value="admin">Admin</option>
                    <option value="pelanggan">Pelanggan</option>
                </select>
            </div>

            <div class="form-group">
                <label for="photo">Photo</label>
                <input type="file"
                    class="form-control-file @error('photo') is-invalid @enderror"
                    name="photo"
                    id="photo"
                    accept="image/*">
                @error('photo')
                <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('user.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@else
<p>You do not have permission to create a user.</p>
@endcan
@endsection