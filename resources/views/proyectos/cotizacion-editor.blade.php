<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2" aria-label="Breadcrumb">
                    <a href="{{ route('proyectos.oportunidades') }}" class="hover:text-slate-700 transition-colors">Oportunidades</a>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <span class="font-medium text-slate-900">{{ $proyecto->cp ?? 'CP-001' }}</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <span class="font-medium text-slate-900">Cotización v{{ $version ?? 2 }}</span>
                </nav>
                <h2 class="text-2xl font-medium text-slate-900">Cotización — {{ $proyecto->nombre ?? 'Texmelucan' }}</h2>
                <p class="mt-1 text-sm text-slate-500">Editor de cotización con costeo de partidas, factores y condiciones comerciales</p>
            </div>
        </div>
    </x-slot>

    <div class="flex gap-6" x-data="cotizacionEditor()" x-init="init()">
        {{-- Left Mini-Nav (15%) --}}
        <div class="w-[15%] shrink-0">
            <nav class="rounded-lg border border-slate-200 bg-white p-3 sticky top-4">
                <ul class="space-y-1">
                    <li>
                        <button
                            @click="activeSection = 'partidas'"
                            :class="activeSection === 'partidas' ? 'bg-gpt-600 text-white' : 'text-slate-700 hover:bg-slate-50'"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                        >
                            <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Partidas
                        </button>
                    </li>
                    <li>
                        <button
                            @click="activeSection = 'factores'"
                            :class="activeSection === 'factores' ? 'bg-gpt-600 text-white' : 'text-slate-700 hover:bg-slate-50'"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                        >
                            <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            Factores
                        </button>
                    </li>
                    <li>
                        <button
                            @click="activeSection = 'condiciones'"
                            :class="activeSection === 'condiciones' ? 'bg-gpt-600 text-white' : 'text-slate-700 hover:bg-slate-50'"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                        >
                            <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Condiciones
                        </button>
                    </li>
                    <li>
                        <button
                            @click="activeSection = 'preview'"
                            :class="activeSection === 'preview' ? 'bg-gpt-600 text-white' : 'text-slate-700 hover:bg-slate-50'"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                        >
                            <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Preview PDF
                        </button>
                    </li>
                </ul>
            </nav>
        </div>

        {{-- Center Editor (60%) --}}
        <div class="w-[60%] space-y-6">

            {{-- Section 1: Partidas --}}
            <div x-show="activeSection === 'partidas'" class="rounded-lg border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-slate-900">Partidas</h3>
                    <span class="text-sm text-slate-500">{{ count($partidas ?? []) ?: 19 }} partidas</span>
                </div>

                <div class="overflow-x-auto -mx-6">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-20">N° Partida</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Descripción</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 w-28">Cantidad</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-24">Unidad</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 w-36">Costo Unitario</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 w-36">Costo Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="(partida, index) in partidas" :key="index">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2">
                                        <input type="text" x-model="partida.numero" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600 text-center" placeholder="N°">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" x-model="partida.descripcion" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Descripción de la partida">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" x-model="partida.cantidad" step="any" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm text-right focus:border-gpt-600 focus:ring-gpt-600" @input="recalcular()">
                                    </td>
                                    <td class="px-4 py-2">
                                        <select x-model="partida.unidad" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
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
                                            <input type="number" x-model="partida.costoUnitario" step="0.01" min="0" class="w-full rounded-lg border border-slate-200 bg-white pl-6 pr-2 py-1.5 text-sm text-slate-900 shadow-sm text-right focus:border-gpt-600 focus:ring-gpt-600" @input="recalcular()">
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-right text-sm font-medium text-slate-900">
                                        <span x-text="formatMXN((partida.cantidad || 0) * (partida.costoUnitario || 0))"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-slate-700">Costo Directo Total</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900" x-text="formatMXN(costoDirecto)"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4">
                    <button type="button" @click="agregarPartida()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Agregar partida
                    </button>
                </div>
            </div>

            {{-- Section 2: Factores --}}
            <div x-show="activeSection === 'factores'" class="rounded-lg border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-slate-900">Factores</h3>
                </div>

                <div class="space-y-6">
                    {{-- Indirectos --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-slate-700">Indirectos (%)</label>
                            <span class="text-sm font-semibold text-slate-900" x-text="factores.indirectos + '%'"></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <input type="range" x-model="factores.indirectos" min="0" max="30" step="0.5" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-gpt-600" @input="recalcular()">
                            <input type="number" x-model="factores.indirectos" min="0" max="30" step="0.5" class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600" @input="recalcular()">
                            <span class="text-sm text-slate-500 w-32 text-right" x-text="formatMXN(costoDirecto * factores.indirectos / 100)"></span>
                        </div>
                    </div>

                    {{-- Administración --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-slate-700">Administración (%)</label>
                            <span class="text-sm font-semibold text-slate-900" x-text="factores.admin + '%'"></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <input type="range" x-model="factores.admin" min="0" max="25" step="0.5" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-gpt-600" @input="recalcular()">
                            <input type="number" x-model="factores.admin" min="0" max="25" step="0.5" class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600" @input="recalcular()">
                            <span class="text-sm text-slate-500 w-32 text-right" x-text="formatMXN(subtotal * factores.admin / 100)"></span>
                        </div>
                    </div>

                    {{-- Utilidad --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-slate-700">Utilidad (%)</label>
                            <span class="text-sm font-semibold text-slate-900" x-text="factores.utilidad + '%'"></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <input type="range" x-model="factores.utilidad" min="0" max="40" step="0.5" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-gpt-600" @input="recalcular()">
                            <input type="number" x-model="factores.utilidad" min="0" max="40" step="0.5" class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600" @input="recalcular()">
                            <span class="text-sm text-slate-500 w-32 text-right" x-text="formatMXN(subtotalAdmin * factores.utilidad / 100)"></span>
                        </div>
                    </div>

                    {{-- Impacto en vivo --}}
                    <div class="border-t border-slate-100 pt-4 mt-6">
                        <h4 class="text-sm font-medium text-slate-700 mb-3">Vista previa del impacto</h4>
                        <div class="grid grid-cols-4 gap-4 text-center">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <span class="block text-xs text-slate-500">Costo Directo</span>
                                <span class="text-sm font-semibold text-slate-900" x-text="formatMXN(costoDirecto)"></span>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <span class="block text-xs text-slate-500">+ Indirectos</span>
                                <span class="text-sm font-semibold text-slate-900" x-text="formatMXN(subtotal - costoDirecto)"></span>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <span class="block text-xs text-slate-500">+ Admin</span>
                                <span class="text-sm font-semibold text-slate-900" x-text="formatMXN(subtotalAdmin - subtotal)"></span>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <span class="block text-xs text-slate-500">+ Utilidad</span>
                                <span class="text-sm font-semibold text-slate-900" x-text="formatMXN(precioCalculado - subtotalAdmin)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Condiciones Comerciales --}}
            <div x-show="activeSection === 'condiciones'" class="rounded-lg border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-slate-900">Condiciones Comerciales</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Anticipo (%)</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="number" x-model="condiciones.anticipo" min="0" max="100" step="1" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <span class="text-sm text-slate-500">%</span>
                            <span class="ml-2 text-sm font-medium text-slate-700" x-text="formatMXN(precioFinal * condiciones.anticipo / 100)"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Contra-entrega (%)</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="number" x-model="condiciones.contraEntrega" min="0" max="100" step="1" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <span class="text-sm text-slate-500">%</span>
                            <span class="ml-2 text-sm font-medium text-slate-700" x-text="formatMXN(precioFinal * condiciones.contraEntrega / 100)"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Validez de la oferta</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="number" x-model="condiciones.validez" min="1" step="1" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <span class="text-sm text-slate-500">días</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tiempo de entrega</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="number" x-model="condiciones.tiempoEntrega" min="1" step="1" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <select x-model="condiciones.unidadTiempo" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="dias">días</option>
                                <option value="semanas">semanas</option>
                                <option value="meses">meses</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-t border-slate-100 pt-4">
                    <label class="block text-sm font-medium text-slate-700">Notas adicionales</label>
                    <textarea x-model="condiciones.notas" rows="3" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600" placeholder="Observaciones, exclusiones, alcances..."></textarea>
                </div>
            </div>

            {{-- Section 4: Preview PDF --}}
            <div x-show="activeSection === 'preview'" class="rounded-lg border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-slate-900">Vista Previa PDF</h3>
                </div>

                <div class="rounded-lg border border-slate-100 bg-slate-50 p-8 min-h-[600px]">
                    <div class="max-w-2xl mx-auto">
                        <div class="text-center mb-8">
                            <h4 class="text-xl font-semibold text-slate-900">{{ $proyecto->nombre ?? 'Texmelucan' }}</h4>
                            <p class="text-sm text-slate-500 mt-1">{{ $proyecto->cp ?? 'CP-001' }} — Cotización v{{ $version ?? 2 }}</p>
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
                                        <template x-for="(partida, index) in partidas.filter(p => p.descripcion)" :key="index">
                                            <tr class="border-b border-slate-100">
                                                <td class="py-2 text-slate-900" x-text="partida.numero"></td>
                                                <td class="py-2 text-slate-900" x-text="partida.descripcion"></td>
                                                <td class="py-2 text-right text-slate-900" x-text="partida.cantidad"></td>
                                                <td class="py-2 text-slate-900" x-text="partida.unidad"></td>
                                                <td class="py-2 text-right text-slate-900" x-text="formatMXN(partida.costoUnitario || 0)"></td>
                                                <td class="py-2 text-right font-medium text-slate-900" x-text="formatMXN((partida.cantidad || 0) * (partida.costoUnitario || 0))"></td>
                                            </tr>
                                        </template>
                                        <tr class="border-t-2 border-slate-300">
                                            <td colspan="5" class="py-3 text-right font-medium text-slate-900">Subtotal</td>
                                            <td class="py-3 text-right font-semibold text-slate-900" x-text="formatMXN(precioFinal)"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="border-t border-slate-200 pt-4 mt-4">
                                <p class="text-sm text-slate-700"><span class="font-medium">Condiciones:</span> Anticipo <span x-text="condiciones.anticipo + '%'"></span>, Contra-entrega <span x-text="condiciones.contraEntrega + '%'"></span></p>
                                <p class="text-sm text-slate-700"><span class="font-medium">Validez:</span> <span x-text="condiciones.validez + ' días'"></span></p>
                                <p class="text-sm text-slate-700"><span class="font-medium">Tiempo de entrega:</span> <span x-text="condiciones.tiempoEntrega + ' ' + condiciones.unidadTiempo"></span></p>
                                <p class="text-sm text-slate-700 mt-2" x-show="condiciones.notas"><span class="font-medium">Notas:</span> <span x-text="condiciones.notas"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Sidebar (25% stickied) --}}
        <div class="w-[25%] shrink-0">
            <div class="sticky top-4 space-y-4">

                {{-- Resumen Card --}}
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Resumen de Cotización</h3>

                    <dl class="space-y-3">
                        <div class="flex justify-between items-baseline">
                            <dt class="text-sm text-slate-500">Costo Directo</dt>
                            <dd class="text-sm font-medium text-slate-900" x-text="formatMXN(costoDirecto)"></dd>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <dt class="text-sm text-slate-500">Indirectos (<span x-text="factores.indirectos + '%'"></span>)</dt>
                            <dd class="text-sm font-medium text-slate-900" x-text="formatMXN(costoDirecto * factores.indirectos / 100)"></dd>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <dt class="text-sm text-slate-500">Administración (<span x-text="factores.admin + '%'"></span>)</dt>
                            <dd class="text-sm font-medium text-slate-900" x-text="formatMXN(subtotal * factores.admin / 100)"></dd>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <dt class="text-sm text-slate-500">Utilidad (<span x-text="factores.utilidad + '%'"></span>)</dt>
                            <dd class="text-sm font-medium text-slate-900" x-text="formatMXN(subtotalAdmin * factores.utilidad / 100)"></dd>
                        </div>

                        <div class="border-t border-slate-200 pt-3">
                            <div class="flex justify-between items-baseline">
                                <dt class="text-sm font-medium text-slate-700">Precio Venta Calculado</dt>
                                <dd class="text-sm font-semibold text-slate-900" x-text="formatMXN(precioCalculado)"></dd>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ajuste Manual (±)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">$</span>
                                <input type="number" x-model="ajusteManual" step="0.01" class="w-full rounded-lg border border-slate-200 bg-white pl-7 pr-3 py-2 text-sm text-slate-900 text-right shadow-sm focus:border-gpt-600 focus:ring-gpt-600" @input="recalcular()">
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-3">
                            <div class="flex justify-between items-baseline">
                                <dt class="text-sm font-medium text-slate-700">Precio Venta Final</dt>
                                <dd class="text-lg font-bold text-slate-900" x-text="formatMXN(precioFinal)"></dd>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-500">Margen Neto</span>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': margenNeto > 30,
                                        'bg-amber-100 text-amber-800': margenNeto >= 20 && margenNeto <= 30,
                                        'bg-gpt-red-100 text-gpt-red-800': margenNeto < 20
                                    }"
                                    x-text="margenNeto.toFixed(2) + '%'"
                                ></span>
                            </div>
                        </div>
                    </dl>
                </div>

                {{-- Tech Reference --}}
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-2">Ref. Técnica</h3>
                    <p class="text-sm text-slate-600">{{ $proyecto->ref_tecnica ?? '—' }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $proyecto->cliente->nombre ?? 'Cliente' }}</p>
                </div>

                {{-- Version Selector --}}
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-2">Versión</h3>
                    <select class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        <option value="1">v1 — {{ now()->subDays(7)->format('d/m/Y') }}</option>
                        <option value="2" selected>v2 — {{ now()->format('d/m/Y') }} (actual)</option>
                    </select>
                </div>

                {{-- Botones de Acción --}}
                <div class="space-y-2">
                    <button type="button" class="w-full rounded-lg bg-gpt-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Guardar borrador
                    </button>
                    <button type="button" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Vista previa PDF
                    </button>
                    <button type="button" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Exportar Excel
                    </button>
                </div>

                {{-- Historial de versiones --}}
                <div class="rounded-lg border border-slate-200 bg-white p-5" x-data="{ historialAbierto: false }">
                    <button type="button" @click="historialAbierto = !historialAbierto" class="flex items-center justify-between w-full text-sm font-semibold text-slate-900">
                        <span>Historial de versiones</span>
                        <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': historialAbierto }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="historialAbierto" x-collapse class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                            <div>
                                <span class="font-medium text-slate-900">v2</span>
                                <span class="text-slate-400 ml-2 text-xs">{{ now()->format('d/m/Y H:i') }}</span>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-gpt-100 text-gpt-800 px-2 py-0.5 text-xs font-medium">Actual</span>
                        </div>
                        <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                            <div>
                                <span class="font-medium text-slate-700">v1</span>
                                <span class="text-slate-400 ml-2 text-xs">{{ now()->subDays(7)->format('d/m/Y H:i') }}</span>
                            </div>
                            <span class="text-xs text-slate-400">${{ number_format(145000, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function cotizacionEditor() {
            return {
                activeSection: 'partidas',
                factores: { indirectos: 10, admin: 12, utilidad: 25 },
                condiciones: { anticipo: 50, contraEntrega: 50, validez: 30, tiempoEntrega: 60, unidadTiempo: 'dias', notas: '' },
                ajusteManual: 0,
                partidas: [],

                get costoDirecto() {
                    return this.partidas.reduce((sum, p) => sum + ((p.cantidad || 0) * (p.costoUnitario || 0)), 0);
                },
                get subtotal() {
                    return this.costoDirecto * (1 + this.factores.indirectos / 100);
                },
                get subtotalAdmin() {
                    return this.subtotal * (1 + this.factores.admin / 100);
                },
                get precioCalculado() {
                    return this.subtotalAdmin * (1 + this.factores.utilidad / 100);
                },
                get precioFinal() {
                    return this.precioCalculado + parseFloat(this.ajusteManual || 0);
                },
                get margenNeto() {
                    if (this.precioFinal === 0 || this.costoDirecto === 0) return 0;
                    return ((this.precioFinal - this.costoDirecto) / this.precioFinal) * 100;
                },

                init() {
                    var self = this;
                    @if(isset($partidas) && count($partidas) > 0)
                        self.partidas = @json($partidas);
                    @else
                        var placeholderNames = [
                            'Suministro de materiales principales',
                            'Mano de obra instalación',
                            'Equipo de bombeo',
                            'Tubería y accesorios',
                            'Sistema eléctrico y control',
                            'Obra civil preliminar',
                            'Montaje de estructuras',
                            'Pruebas y puesta en marcha',
                            'Transporte y fletes',
                            'Supervisión técnica',
                            'Andamios y maniobras',
                            'Cimentación de equipos',
                            'Sistema contra incendio',
                            'Aislamiento térmico',
                            'Pintura y acabados',
                            'Ingeniería de detalle',
                            'Gestión de permisos',
                            'Capacitación al personal',
                            'Garantía y soporte'
                        ];
                        for (var i = 0; i < 19; i++) {
                            self.partidas.push({
                                numero: 'P-' + String(i + 1).padStart(2, '0'),
                                descripcion: placeholderNames[i] || '',
                                cantidad: Math.floor(Math.random() * 15) + 1,
                                unidad: ['pza','kg','m','m2','lote','servicio'][Math.floor(Math.random() * 6)],
                                costoUnitario: parseFloat((Math.random() * 50000 + 500).toFixed(2))
                            });
                        }
                    @endif
                },

                recalcular() {},

                agregarPartida() {
                    this.partidas.push({
                        numero: 'P-' + String(this.partidas.length + 1).padStart(2, '0'),
                        descripcion: '',
                        cantidad: 1,
                        unidad: 'pza',
                        costoUnitario: 0
                    });
                },

                formatMXN(val) {
                    return '$ ' + new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val || 0);
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
