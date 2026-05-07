<div x-data="{ selectedRole: '' }" x-init="selectedRole = $refs.roleSelect.options[$refs.roleSelect.selectedIndex].getAttribute('data-slug')">
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        @error('name') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
    </div>

    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight">
        @error('email') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
    </div>

    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Peran (Role)</label>
        <select 
            name="role_id" 
            id="role_id" 
            x-ref="roleSelect"
            @change="selectedRole = $el.options[$el.selectedIndex].getAttribute('data-slug')"
            required
        >
            <option value="">Pilih Role</option>
            @foreach($roles as $role)
                <option 
                    value="{{ $role->id }}" 
                    data-slug="{{ $role->slug }}"
                    {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}
                >
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        @error('role_id') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
    </div>

    <div x-show="selectedRole === 'agent' || selectedRole === 'supervisor'" class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Tim</label>
        <select name="team_id" class="shadow border rounded w-full py-2 px-3 text-gray-700">
            <option value="">Tanpa Tim</option>
            @foreach($teams as $team)
                <option value="{{ $team->id }}" {{ (old('team_id', $user->team_id ?? '') == $team->id) ? 'selected' : '' }}>{{ $team->name }}</option>
            @endforeach
        </select>
        @error('team_id') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
    </div>

    @if(!isset($user))
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
        <input type="password" name="password" class="shadow border rounded w-full py-2 px-3 text-gray-700">
        @error('password') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
    </div>
    @endif
</div>
