<div>
    @section('title', 'Cotización — ' . ($proyecto->cp_numero ?? 'CP'))

    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2" aria-label="Breadcrumb">
                <a href="{{ route('oportunidades.index') }}" class="hover:text-slate-700 transition-colors">Oportunidades</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-900">{{ $proyecto->cp_numero }}</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                @if($cotizacionActiva)
                    <span class="font-medium text-slate-900">Cotización v{{ $cotizacionActiva->version }}</span>
                @else
                    <span class="font-medium text-slate-900">Nueva cotización</span>
                @endif
            </nav>
            <h2 class="text-2xl font-medium text-slate-900">Cotización — {{ $proyecto->cliente->razon_social ?? '—' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Editor de cotización con costeo de partidas, factores y condiciones comerciales</p>
        </div>
    </div>

    @if($successMessage)
        <div wire:transition.opacity.duration.500ms class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">{{ $successMessage }}</div>
    @endif

    <div class="mt-6 flex gap-6">
        {{-- Left Mini-Nav --}}
        <div class="w-[15%] shrink-0">
            <nav class="rounded-lg border border-slate-200 bg-white p-3 sticky top-4">
                <ul class="space-y-1">
                    @foreach(['partidas' => 'Partidas', 'factores' => 'Factores', 'condiciones' => 'Condiciones', 'preview' => 'Preview PDF'] as $key => $label)
                        <li>
                            <button wire:click="$set('activeSection', '{{ $key }}')" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeSection === $key ? 'bg-gpt-600 text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                                {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        {{-- Center Editor --}}
        <div class="w-[60%] space-y-6">

            {{-- Section 1: Partidas --}}
            @if($activeSection === 'partidas')
                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-slate-900">Partidas</h3>
                        <span class="text-sm text-slate-500">{{ count($partidas) }} partidas</span>
                    </div>

                    <div class="overflow-x-auto -mx-6">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-20">N°</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Descripción</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 w-28">Cantidad</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-24">Unidad</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 w-36">Costo Unitario</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 w-36">Costo Total</th>
                                    <th class="px-4 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($partidas as $idx => $partida)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-2">
                                            <span class="text-sm text-slate-500">{{ $partida['numero'] }}</span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" wire:model="partidas.{{ $idx }}.descripcion" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Descripción de la partida">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="number" wire:model="partidas.{{ $idx }}.cantidad" step="any" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm text-right focus:border-gpt-600 focus:ring-gpt-600">
                                        </td>
                                        <td class="px-4 py-2">
                                            <select wire:model="partidas.{{ $idx }}.unidad" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                                <option value="pza">Pza</option>
                                                <option value="kg">Kg</option>
                                                <option value="m">m</option>
                                                <option value="m2">m²</option>
                                                <option value="m3">m³</option>
                                                <option value="lote">Lote</option>
                                                <option value="servicio">Servicio</option>
                                                <option value="global">Global</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="relative">
                                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-sm text-slate-400">$</span>
                                                <input type="number" wire:model="partidas.{{ $idx }}.costoUnitario" step="0.01" min="0" class="w-full rounded-lg border border-slate-200 bg-white pl-6 pr-2 py-1.5 text-sm text-slate-900 shadow-sm text-right focus:border-gpt-600 focus:ring-gpt-600">
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-right text-sm font-medium text-slate-900">
                                            $ {{ number_format(($partida['cantidad'] ?? 0) * ($partida['costoUnitario'] ?? 0), 2) }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <button wire:click="removerPartida({{ $idx }})" class="rounded p-1 text-slate-400 hover:text-red-600 hover:bg-red-50">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                @if(count($partidas) === 0)
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-400">Sin partidas. Haz clic en "Agregar partida" para comenzar.</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot class="bg-slate-50">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-slate-700">Costo Directo Total</td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">$ {{ number_format($this->costoDirecto, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="button" wire:click="agregarPartida" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Agregar partida
                        </button>
                    </div>
                </div>
            @endif

            {{-- Section 2: Factores --}}
            @if($activeSection === 'factores')
                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-6">Factores</h3>
                    <div class="space-y-6">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-slate-700">Indirectos (%)</label>
                                <span class="text-sm font-semibold text-slate-900">{{ $factores['indirectos'] }}%</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <input type="range" wire:model="factores.indirectos" min="0" max="30" step="0.5" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-gpt-600">
                                <input type="number" wire:model="factores.indirectos" min="0" max="30" step="0.5" class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <span class="text-sm text-slate-500 w-32 text-right">$ {{ number_format($this->costoDirecto * $factores['indirectos'] / 100, 2) }}</span>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-slate-700">Administración (%)</label>
                                <span class="text-sm font-semibold text-slate-900">{{ $factores['admin'] }}%</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <input type="range" wire:model="factores.admin" min="0" max="25" step="0.5" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-gpt-600">
                                <input type="number" wire:model="factores.admin" min="0" max="25" step="0.5" class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <span class="text-sm text-slate-500 w-32 text-right">$ {{ number_format($this->subtotal * $factores['admin'] / 100, 2) }}</span>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-slate-700">Utilidad (%)</label>
                                <span class="text-sm font-semibold text-slate-900">{{ $factores['utilidad'] }}%</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <input type="range" wire:model="factores.utilidad" min="0" max="40" step="0.5" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-gpt-600">
                                <input type="number" wire:model="factores.utilidad" min="0" max="40" step="0.5" class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <span class="text-sm text-slate-500 w-32 text-right">$ {{ number_format($this->subtotalAdmin * $factores['utilidad'] / 100, 2) }}</span>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-4 mt-6">
                            <h4 class="text-sm font-medium text-slate-700 mb-3">Vista previa del impacto</h4>
                            <div class="grid grid-cols-4 gap-4 text-center">
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <span class="block text-xs text-slate-500">Costo Directo</span>
                                    <span class="text-sm font-semibold text-slate-900">$ {{ number_format($this->costoDirecto, 2) }}</span>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <span class="block text-xs text-slate-500">+ Indirectos</span>
                                    <span class="text-sm font-semibold text-slate-900">$ {{ number_format($this->subtotal - $this->costoDirecto, 2) }}</span>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <span class="block text-xs text-slate-500">+ Admin</span>
                                    <span class="text-sm font-semibold text-slate-900">$ {{ number_format($this->subtotalAdmin - $this->subtotal, 2) }}</span>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <span class="block text-xs text-slate-500">+ Utilidad</span>
                                    <span class="text-sm font-semibold text-slate-900">$ {{ number_format($this->precioCalculado - $this->subtotalAdmin, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Section 3: Condiciones --}}
            @if($activeSection === 'condiciones')
                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-6">Condiciones Comerciales</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Anticipo (%)</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="number" wire:model="condiciones.anticipo" min="0" max="100" step="1" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <span class="text-sm text-slate-500">%</span>
                                <span class="ml-2 text-sm font-medium text-slate-700">$ {{ number_format($this->precioFinal * ($condiciones['anticipo'] ?? 0) / 100, 2) }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Contra-entrega (%)</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="number" wire:model="condiciones.contraEntrega" min="0" max="100" step="1" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <span class="text-sm text-slate-500">%</span>
                                <span class="ml-2 text-sm font-medium text-slate-700">$ {{ number_format($this->precioFinal * ($condiciones['contraEntrega'] ?? 0) / 100, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Validez de la oferta</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="number" wire:model="condiciones.validez" min="1" step="1" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <span class="text-sm text-slate-500">días</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Tiempo de entrega</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="number" wire:model="condiciones.tiempoEntrega" min="1" step="1" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <select wire:model="condiciones.unidadTiempo" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                    <option value="dias">días</option>
                                    <option value="semanas">semanas</option>
                                    <option value="meses">meses</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <label class="block text-sm font-medium text-slate-700">Moneda</label>
                        <select wire:model="moneda" class="mt-1 w-40 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <option value="USD">USD</option>
                            <option value="MXN">MXN</option>
                        </select>
                    </div>
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <label class="block text-sm font-medium text-slate-700">Notas adicionales</label>
                        <textarea wire:model="condiciones.notas" rows="3" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600" placeholder="Observaciones, exclusiones, alcances..."></textarea>
                    </div>
                </div>
            @endif

            {{-- Section 4: Preview --}}
            @if($activeSection === 'preview')
                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Vista Previa PDF</h3>
                    <div class="rounded-lg border border-slate-100 bg-slate-50 p-8 min-h-[600px]">
                        <div class="max-w-2xl mx-auto">
                            <div class="text-center mb-8">
                                <h4 class="text-xl font-semibold text-slate-900">{{ $proyecto->cliente->razon_social ?? '—' }}</h4>
                                <p class="text-sm text-slate-500 mt-1">{{ $proyecto->cp_numero }} — Cotización v{{ $cotizacionActiva->version ?? 1 }}</p>
                            </div>
                            <div class="space-y-4">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                                        <thead>
                                            <tr class="border-b border-slate-200">
                                                <th class="pb-2 text-left text-xs font-semibold text-slate-500">N°</th>
                                                <th class="pb-2 text-left text-xs font-semibold text-slate-500">Descripción</th>
                                                <th class="pb-2 text-right text-xs font-semibold text-slate-500">Cant.</th>
                                                <th class="pb-2 text-left text-xs font-semibold text-slate-500">Und.</th>
                                                <th class="pb-2 text-right text-xs font-semibold text-slate-500">Costo Unit.</th>
                                                <th class="pb-2 text-right text-xs font-semibold text-slate-500">Costo Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($partidas as $p)
                                                @if(!empty($p['descripcion']))
                                                    <tr class="border-b border-slate-100">
                                                        <td class="py-2 text-slate-900">{{ $p['numero'] }}</td>
                                                        <td class="py-2 text-slate-900">{{ $p['descripcion'] }}</td>
                                                        <td class="py-2 text-right text-slate-900">{{ $p['cantidad'] }}</td>
                                                        <td class="py-2 text-slate-900">{{ $p['unidad'] }}</td>
                                                        <td class="py-2 text-right text-slate-900">$ {{ number_format($p['costoUnitario'] ?? 0, 2) }}</td>
                                                        <td class="py-2 text-right font-medium text-slate-900">$ {{ number_format(($p['cantidad'] ?? 0) * ($p['costoUnitario'] ?? 0), 2) }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            <tr class="border-t-2 border-slate-300">
                                                <td colspan="5" class="py-3 text-right font-medium text-slate-900">Precio Venta Final</td>
                                                <td class="py-3 text-right font-semibold text-slate-900">$ {{ number_format($this->precioFinal, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="border-t border-slate-200 pt-4 mt-4">
                                    @if(!empty($condiciones['anticipo']) || !empty($condiciones['contraEntrega']))
                                        <p class="text-sm text-slate-700"><span class="font-medium">Condiciones:</span> Anticipo {{ $condiciones['anticipo'] ?? 0 }}%, Contra-entrega {{ $condiciones['contraEntrega'] ?? 0 }}%</p>
                                    @endif
                                    @if(!empty($condiciones['validez']))
                                        <p class="text-sm text-slate-700"><span class="font-medium">Validez:</span> {{ $condiciones['validez'] }} días</p>
                                    @endif
                                    @if(!empty($condiciones['tiempoEntrega']))
                                        <p class="text-sm text-slate-700"><span class="font-medium">Tiempo de entrega:</span> {{ $condiciones['tiempoEntrega'] }} {{ $condiciones['unidadTiempo'] ?? 'dias' }}</p>
                                    @endif
                                    @if(!empty($condiciones['notas']))
                                        <p class="text-sm text-slate-700 mt-2"><span class="font-medium">Notas:</span> {{ $condiciones['notas'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Sidebar --}}
        <div class="w-[25%] shrink-0">
            <div class="sticky top-4 space-y-4">
                {{-- Resumen Card --}}
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Resumen de Cotización</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between items-baseline">
                            <dt class="text-sm text-slate-500">Costo Directo</dt>
                            <dd class="text-sm font-medium text-slate-900">$ {{ number_format($this->costoDirecto, 2) }}</dd>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <dt class="text-sm text-slate-500">Indirectos ({{ $factores['indirectos'] }}%)</dt>
                            <dd class="text-sm font-medium text-slate-900">$ {{ number_format($this->costoDirecto * $factores['indirectos'] / 100, 2) }}</dd>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <dt class="text-sm text-slate-500">Administración ({{ $factores['admin'] }}%)</dt>
                            <dd class="text-sm font-medium text-slate-900">$ {{ number_format($this->subtotal * $factores['admin'] / 100, 2) }}</dd>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <dt class="text-sm text-slate-500">Utilidad ({{ $factores['utilidad'] }}%)</dt>
                            <dd class="text-sm font-medium text-slate-900">$ {{ number_format($this->subtotalAdmin * $factores['utilidad'] / 100, 2) }}</dd>
                        </div>

                        <div class="border-t border-slate-200 pt-3">
                            <div class="flex justify-between items-baseline">
                                <dt class="text-sm font-medium text-slate-700">Precio Venta Calculado</dt>
                                <dd class="text-sm font-semibold text-slate-900">$ {{ number_format($this->precioCalculado, 2) }}</dd>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ajuste Manual (±)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">$</span>
                                <input type="number" wire:model="ajusteManual" step="0.01" class="w-full rounded-lg border border-slate-200 bg-white pl-7 pr-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-3">
                            <div class="flex justify-between items-baseline">
                                <dt class="text-sm font-medium text-slate-700">Precio Venta Final</dt>
                                <dd class="text-lg font-bold text-slate-900">$ {{ number_format($this->precioFinal, 2) }}</dd>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-500">Margen Neto</span>
                                @php $margen = $this->margenNeto; @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $margen > 30 ? 'bg-green-100 text-green-800' : ($margen >= 20 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($margen, 2) }}%
                                </span>
                            </div>
                        </div>
                    </dl>
                </div>

                {{-- Ref. Técnica --}}
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-2">Ref. Técnica</h3>
                    <p class="text-sm text-slate-600">{{ $proyecto->tech_reference ?? '—' }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $proyecto->cliente->razon_social ?? '—' }}</p>
                </div>

                {{-- Version Selector --}}
                @if($cotizaciones->count() > 0)
                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-slate-900 mb-2">Versión</h3>
                        <select wire:change="changeVersion($event.target.value)" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            @foreach($cotizaciones as $cot)
                                <option value="{{ $cot->id }}" {{ $selectedVersion == $cot->id ? 'selected' : '' }}>
                                    v{{ $cot->version }} — {{ $cot->created_at->format('d/m/Y') }} {{ $cot->id === ($cotizacionActiva->id ?? null) ? '(actual)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Botones de Acción --}}
                <div class="space-y-2">
                    <button type="button" wire:click="guardarBorrador" class="w-full rounded-lg bg-gpt-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors" wire:loading.attr="disabled">
                        <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Guardar borrador
                    </button>
                    <button type="button" wire:click="enviarRevision" class="w-full rounded-lg border border-gpt-600 bg-white px-4 py-2.5 text-sm font-medium text-gpt-600 shadow-sm hover:bg-gpt-50 transition-colors" wire:loading.attr="disabled">
                        <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-7"/></svg>
                        Enviar a revisión
                    </button>
                </div>

                {{-- Historial de versiones --}}
                @if($cotizaciones->count() > 0)
                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Historial de versiones</h3>
                        <div class="space-y-2">
                            @foreach($cotizaciones as $cot)
                                <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                                    <div>
                                        <span class="font-medium text-slate-900">v{{ $cot->version }}</span>
                                        <span class="text-slate-400 ml-2 text-xs">{{ $cot->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if($cot->id === ($cotizacionActiva->id ?? null))
                                        <span class="inline-flex items-center rounded-full bg-gpt-100 text-gpt-800 px-2 py-0.5 text-xs font-medium">Actual</span>
                                    @else
                                        <span class="text-xs text-slate-400">$ {{ number_format($cot->precio_venta_final, 2) }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>