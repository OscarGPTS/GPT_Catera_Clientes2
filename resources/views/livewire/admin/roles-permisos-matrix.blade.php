<div>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Roles y permisos</h2>
            <p class="mt-1 text-sm text-slate-500">Roles del sistema con sus permisos asignados. Los permisos se modifican solo por código.</p>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Rol</th>
                    @foreach($groupedPermissions as $group => $perms)
                        <th colspan="{{ $perms->count() }}" class="px-2 py-2 text-center text-xs font-medium uppercase tracking-wider text-slate-500 border-b border-slate-200">{{ ucfirst($group) }}</th>
                    @endforeach
                </tr>
                <tr>
                    <th class="sticky left-0 z-10 bg-slate-50"></th>
                    @foreach($permissions as $perm)
                        <th class="px-1 py-1 text-center">
                            <span class="block max-w-[60px] truncate text-[10px] text-slate-400" title="{{ $perm->name }}">{{ explode(' ', $perm->name, 2)[1] ?? $perm->name }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($roles as $role)
                <tr class="hover:bg-slate-50">
                    <td class="sticky left-0 z-10 whitespace-nowrap bg-white px-4 py-3 text-sm font-medium text-slate-900">
                        <span class="inline-flex items-center rounded-full bg-gpt-100 px-2.5 py-0.5 text-xs font-medium text-gpt-800">{{ $role->name }}</span>
                    </td>
                    @foreach($permissions as $perm)
                        @if($role->hasPermissionTo($perm->name))
                            <td class="px-1 py-1 text-center">
                                <svg class="mx-auto h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </td>
                        @else
                            <td class="px-1 py-1 text-center">
                                <span class="block h-4 w-4 mx-auto rounded border border-slate-200"></span>
                            </td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 rounded-lg border border-gpt-200 bg-gpt-50 p-4">
        <p class="text-sm text-gpt-800">
            <span class="font-medium">Nota:</span> Los permisos se asignan por código en <code class="rounded bg-gpt-100 px-1 py-0.5 text-xs">RolesPermissionsSeeder</code>. Los cambios requieren una migración controlada.
        </p>
    </div>
</div>