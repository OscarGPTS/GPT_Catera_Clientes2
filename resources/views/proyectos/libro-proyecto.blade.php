<x-layouts.app>
    <x-slot name="header">
        <div>
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2" aria-label="Breadcrumb">
                <a href="{{ route('proyectos.index') }}" class="hover:text-slate-700 transition-colors">Proyectos</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-900">{{ $proyecto->dn ?? $proyecto->nombre ?? 'DN-001' }}</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-900">Libro de Proyecto</span>
            </nav>
            <h2 class="text-2xl font-medium text-slate-900">Libro de Proyecto — {{ $proyecto->nombre ?? 'Proyecto' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Dossier ISO — Control documental de ejecución</p>
        </div>
    </x-slot>

    <div x-data="libroProyecto()" x-init="init()">
        {{-- Header: project name, % avance global --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-slate-500">Avance global del dossier</h3>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-4xl font-bold text-slate-900" x-text="porcentajeGlobal.toFixed(0) + '%'"></span>
                        <span class="text-sm text-slate-400" x-text="'(' + itemsCompletados + '/' + itemsTotales + ' items)'"></span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="w-48 h-4 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gpt-600 transition-all duration-500" :style="'width: ' + porcentajeGlobal + '%'"></div>
                    </div>
                    <button type="button"
                            x-show="porcentajeGlobal >= 100"
                            class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Generar PDF consolidado
                    </button>
                </div>
            </div>
        </div>

        {{-- Accordion Sections --}}
        <div class="space-y-3">
            <template x-for="(seccion, index) in secciones" :key="index">
                <div
                    class="rounded-lg border shadow-sm transition-colors"
                    :class="seccion.completada ? 'border-l-4 border-l-green-500 border-slate-200 bg-white' : 'border-slate-200 bg-white'"
                >
                    {{-- Accordion Header --}}
                    <button
                        type="button"
                        @click="seccion.abierta = !seccion.abierta"
                        class="flex items-center w-full px-5 py-4 text-left hover:bg-slate-50 transition-colors"
                    >
                        {{-- Badge código --}}
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white mr-4" x-text="seccion.codigo"></span>

                        {{-- Nombre y completado --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-slate-900" x-text="seccion.nombre"></span>
                                <template x-if="seccion.completada">
                                    <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </template>
                            </div>
                            <div class="mt-1 flex items-center gap-3">
                                <div class="h-2 w-32 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-gpt-600 transition-all duration-300" :style="'width: ' + seccion.porcentaje + '%'"></div>
                                </div>
                                <span class="text-xs text-slate-500" x-text="seccion.porcentaje.toFixed(0) + '%'"></span>
                            </div>
                        </div>

                        {{-- Chevron --}}
                        <svg
                            class="h-5 w-5 text-slate-400 transition-transform duration-200 shrink-0 ml-4"
                            :class="{ 'rotate-180': seccion.abierta }"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        ><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    {{-- Accordion Body --}}
                    <div x-show="seccion.abierta" x-collapse>
                        <div class="border-t border-slate-100 px-5 py-5 space-y-6">

                            {{-- Checklist --}}
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900 mb-3">Checklist de entregables</h4>
                                <div class="space-y-2">
                                    <template x-for="(item, i) in seccion.checklist" :key="i">
                                        <label class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
                                            <input type="checkbox"
                                                   x-model="item.completado"
                                                   @change="recalcular()"
                                                   class="mt-0.5 h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                                            <span
                                                class="text-sm transition-colors"
                                                :class="item.completado ? 'text-slate-400 line-through' : 'text-slate-700'"
                                                x-text="item.descripcion"
                                            ></span>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            {{-- File Upload Area --}}
                            <div class="border-t border-slate-100 pt-5">
                                <h4 class="text-sm font-semibold text-slate-900 mb-3">Documentos cargados</h4>

                                {{-- Drag & Drop Zone --}}
                                <div
                                    class="rounded-lg border-2 border-dashed border-slate-200 p-6 text-center hover:border-gpt-600 transition-colors cursor-pointer"
                                    @dragover.prevent="$el.classList.add('border-gpt-600', 'bg-gpt-50')"
                                    @dragleave.prevent="$el.classList.remove('border-gpt-600', 'bg-gpt-50')"
                                    @drop.prevent="handleDrop($event, index); $el.classList.remove('border-gpt-600', 'bg-gpt-50')"
                                >
                                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1"/></svg>
                                    <p class="mt-2 text-sm text-slate-500">Arrastra archivos aquí</p>
                                    <p class="text-xs text-slate-400 mt-0.5">PDF, Word, Excel, DWG — máx. 25 MB</p>
                                </div>

                                {{-- Uploaded docs list --}}
                                <div class="mt-4 space-y-2">
                                    <template x-for="(doc, d) in seccion.documentos" :key="d">
                                        <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 group">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <svg class="h-8 w-8 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium text-slate-900 truncate" x-text="doc.nombre"></p>
                                                    <p class="text-xs text-slate-400">
                                                        <span x-text="'v' + doc.version"></span> &middot;
                                                        <span x-text="doc.tamano"></span> &middot;
                                                        <span x-text="doc.subidoPor"></span> &middot;
                                                        <span x-text="doc.fecha"></span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a :href="doc.url ?? '#'" class="p-1.5 rounded-lg text-slate-400 hover:text-gpt-600 hover:bg-slate-100 transition-colors" title="Descargar">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                </a>
                                                <button type="button" @click="eliminarDocumento(index, d)" class="p-1.5 rounded-lg text-slate-400 hover:text-gpt-red-600 hover:bg-slate-100 transition-colors" title="Eliminar">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <p x-show="seccion.documentos.length === 0" class="text-sm text-slate-400 text-center py-4">Sin documentos cargados en esta sección</p>
                                </div>

                                <button type="button" @click="abrirFileInput(index)" class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    Subir archivo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Bottom actions --}}
        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                Guardar avance
            </button>
            <button type="button" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                Exportar estatus
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        function libroProyecto() {
            return {
                secciones: [
                    { codigo: 'A', nombre: 'Cronograma de Actividades', porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                    { codigo: 'B', nombre: 'Ingeniería de Proyecto',     porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                    { codigo: 'C', nombre: 'Permisos',                    porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                    { codigo: 'D', nombre: 'Estudios',                    porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                    { codigo: 'E', nombre: 'Procedimientos',              porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                    { codigo: 'F', nombre: 'Certificados',                porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                    { codigo: 'G', nombre: 'Registro de Pruebas',         porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                    { codigo: 'H', nombre: 'Seguridad',                   porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                    { codigo: 'I', nombre: 'Ejecución',                   porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                    { codigo: 'J', nombre: 'Misceláneos',                 porcentaje: 0, abierta: false, completada: false, checklist: [], documentos: [] },
                ],

                checklistPlantillas: {
                    'A': ['Programa general de obra (Gantt)', 'Ruta crítica definida', 'Hitos contractuales identificados', 'Calendario de recursos', 'Plan de adquisiciones'],
                    'B': ['Planos de detalle aprobados', 'Memoria de cálculo', 'Especificaciones técnicas', 'Modelo 3D / BIM', 'Listado de materiales (BOM)', 'Diagramas P&ID'],
                    'C': ['Licencia de construcción', 'Permiso ambiental', 'Factibilidad de servicios', 'Permiso de vialidad', 'Dictamen de protección civil'],
                    'D': ['Estudio de mecánica de suelos', 'Estudio topográfico', 'Estudio de impacto ambiental', 'Dictamen estructural', 'Estudio hidrológico'],
                    'E': ['Procedimiento de soldadura (WPS)', 'Plan de calidad (ITP)', 'Procedimiento de izaje', 'Procedimiento de pruebas', 'Plan de seguridad y salud'],
                    'F': ['Certificados de materiales', 'Certificados de calibración', 'Certificados de competencia laboral', 'Pólizas de garantía', 'Cartas de cumplimiento'],
                    'G': ['Pruebas hidrostáticas', 'Pruebas no destructivas (NDT)', 'Pruebas de funcionamiento', 'Pruebas de integridad', 'Protocolos de comisionamiento'],
                    'H': ['Plan de seguridad', 'Análisis de riesgos (ATS)', 'Registro de capacitación', 'Reportes de inspección', 'Equipo de protección personal'],
                    'I': ['Reportes diarios de avance', 'Registro de mano de obra', 'Bitácora de frentes', 'Minutas de reunión', 'Estimaciones y facturación'],
                    'J': ['Garantías de equipos', 'Fianzas', 'Correspondencia oficial', 'Fotografías de obra', 'Cierre administrativo'],
                },

                init() {
                    var self = this;
                    @if(isset($secciones) && count($secciones) > 0)
                        self.secciones = @json($secciones).map(function(s) {
                            s.abierta = false;
                            return s;
                        });
                    @else
                        this.secciones.forEach(function(seccion) {
                            var codigo = seccion.codigo;
                            var items = self.checklistPlantillas[codigo] || ['Entregable 1', 'Entregable 2', 'Entregable 3'];
                            seccion.checklist = items.map(function(desc) {
                                return { descripcion: desc, completado: Math.random() > 0.6 };
                            });

                            var count = Math.floor(Math.random() * 3);
                            seccion.documentos = [];
                            for (var i = 0; i < count; i++) {
                                seccion.documentos.push({
                                    nombre: codigo + '_Documento_' + (i + 1) + '.pdf',
                                    version: i + 1,
                                    tamano: (Math.random() * 5 + 0.5).toFixed(1) + ' MB',
                                    subidoPor: '{{ auth()->user()->name ?? "Usuario" }}',
                                    fecha: '{{ now()->subDays(rand(1,30))->format("d/m/Y") }}',
                                    url: '#'
                                });
                            }

                            self.recalcularSeccion(seccion);
                        });
                    @endif
                },

                get itemsCompletados() {
                    var count = 0;
                    this.secciones.forEach(function(s) {
                        s.checklist.forEach(function(item) {
                            if (item.completado) count++;
                        });
                    });
                    return count;
                },

                get itemsTotales() {
                    var count = 0;
                    this.secciones.forEach(function(s) {
                        count += s.checklist.length;
                    });
                    return count;
                },

                get porcentajeGlobal() {
                    if (this.itemsTotales === 0) return 0;
                    return (this.itemsCompletados / this.itemsTotales) * 100;
                },

                recalcular() {
                    var self = this;
                    this.secciones.forEach(function(s) {
                        self.recalcularSeccion(s);
                    });
                },

                recalcularSeccion(seccion) {
                    var total = seccion.checklist.length;
                    if (total === 0) {
                        seccion.porcentaje = 0;
                        seccion.completada = false;
                        return;
                    }
                    var completados = seccion.checklist.filter(function(item) { return item.completado; }).length;
                    seccion.porcentaje = (completados / total) * 100;
                    seccion.completada = completados === total;
                },

                handleDrop(event, sectionIndex) {
                    var files = event.dataTransfer.files;
                    for (var i = 0; i < files.length; i++) {
                        this.secciones[sectionIndex].documentos.push({
                            nombre: files[i].name,
                            version: 1,
                            tamano: (files[i].size / 1048576).toFixed(2) + ' MB',
                            subidoPor: '{{ auth()->user()->name ?? "Usuario" }}',
                            fecha: '{{ now()->format("d/m/Y") }}',
                            url: '#'
                        });
                    }
                },

                eliminarDocumento(seccionIndex, docIndex) {
                    this.secciones[seccionIndex].documentos.splice(docIndex, 1);
                },

                abrirFileInput(sectionIndex) {
                    var self = this;
                    var nombreSimulado = 'Nuevo_Documento_' + (self.secciones[sectionIndex].documentos.length + 1) + '.pdf';
                    self.secciones[sectionIndex].documentos.push({
                        nombre: nombreSimulado,
                        version: 1,
                        tamano: (Math.random() * 5 + 0.5).toFixed(1) + ' MB',
                        subidoPor: '{{ auth()->user()->name ?? "Usuario" }}',
                        fecha: '{{ now()->format("d/m/Y") }}',
                        url: '#'
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
