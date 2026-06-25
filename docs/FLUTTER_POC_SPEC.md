# PoC App Móvil Flutter — Especificación para el agente de IA

> **Documento de contexto y contrato** para construir un PoC en Flutter de las
> tres pantallas principales de la cartera comercial de GPT Services:
> **Dashboard (`/`)**, **Oportunidades (`/oportunidades`)** y **Clientes (`/clientes`)**.
>
> Fuente de la verdad: extraído del código Laravel 12 + Livewire 3 de
> `GPT_Catera_Clientes2` (junio 2026).

---

## 0. Contexto arquitectónico — LEER PRIMERO

La app web **NO tiene API REST**. Es una aplicación **Livewire 3 server-rendered**:
las pantallas son componentes PHP que consultan Eloquent directamente y renderizan
HTML. No existe `routes/api.php`; el único endpoint que devuelve JSON es el proxy
del buscador de IA (`/consulta-ia`).

**Implicación para el PoC Flutter:** Flutter no puede hablar con Livewire. Hay dos
caminos:

| Opción | Qué implica | Recomendación para PoC |
|---|---|---|
| **A. Mock local** | El agente genera un backend simulado (JSON estático / `json_server` / Dio interceptors con datos fake) usando los contratos de este doc. | ✅ **Recomendado para el PoC**: cero dependencia del backend real. |
| **B. API REST real** | Implementar en Laravel los endpoints `/api/v1/*` de este doc (controllers + Sanctum/Auth0). | Fase 2, cuando el PoC se valide. |

Este documento define **el contrato REST que ambos caminos deben respetar**, derivado
de la lógica que hoy vive en los componentes Livewire. Los nombres de campo coinciden
1:1 con las columnas reales de la base de datos para que migrar de mock a real sea
transparente.

### Convenciones del contrato

- Base URL propuesta: `{HOST}/api/v1`
- Autenticación: `Authorization: Bearer {token}` (Sanctum o JWT Auth0). Para el PoC con
  mock, ignorar.
- Todas las respuestas en JSON. Montos en **USD** salvo que se indique millones (`_m`).
- Paginación estilo Laravel: `{ data: [...], meta: { current_page, last_page, total, per_page } }`.
- Fechas en ISO 8601 (`YYYY-MM-DD`).

---

## 1. Modelos de datos (referencia transversal)

Estos modelos se comparten entre las tres pantallas.

### 1.1 `Cliente` (tabla `clientes`)

| Campo | Tipo | Notas |
|---|---|---|
| `id` | int | PK |
| `razon_social` | string(255) | Obligatorio. Nombre legal. |
| `alias` | string(30) | Obligatorio, **único**, se guarda en MAYÚSCULAS. |
| `rfc` | string(13) | Opcional, único, MAYÚSCULAS. |
| `sector` | string(100) | Opcional (p.ej. "Defensa", "Energía"). |
| `segmento` | string(100) | Opcional. |
| `activo` | bool | Soft-disable: "eliminar" = `activo: false`. |
| `created_at` / `updated_at` | datetime | |
| `deleted_at` | datetime | SoftDeletes. |

Relaciones: `contactos` (hasMany), `proyectos` (hasMany).

### 1.2 `ContactoCliente` (tabla `contactos_cliente`)

| Campo | Tipo | Notas |
|---|---|---|
| `id` | int | PK |
| `cliente_id` | int | FK |
| `nombre` | string(255) | Obligatorio. |
| `puesto` | string(255) | Opcional. |
| `email` | string(255) | Opcional, validación email. |
| `telefono` | string(50) | Opcional. |
| `principal` | bool | Solo uno por cliente; al marcar uno, los demás pasan a `false`. |

### 1.3 `Proyecto` (tabla `proyectos`) — representa Oportunidades **y** Proyectos

El mismo modelo sirve para ambos; los distingue el campo `tipo` (`oportunidad` | `proyecto`).
La pantalla `/oportunidades` filtra implícitamente por contexto de pipeline comercial.

| Campo | Tipo | Notas |
|---|---|---|
| `id` | int | PK |
| `cp_numero` | string | Número de cotización/propuesta (CP). |
| `dn_numero` | string | Número DN. |
| `tech_reference` | string | Referencia técnica corta (se usa como "nombre"). |
| `anio` | int | Año de la oportunidad. |
| `cliente_id` | int | FK → `clientes`. |
| `contacto` | string | Nombre de contacto (texto libre). |
| `datos_contacto` | string | Datos de contacto (texto libre). |
| `lugar_id` | int | FK → `lugares`. |
| `alcance` | text | Descripción del alcance. |
| `fecha_envio` | date | Fecha de envío de la oferta. |
| `fecha_modificacion_oferta` | date | |
| `monto_usd` | decimal(2) | Monto de la oportunidad en USD. |
| `hitos_pago` | text/json | Hitos de pago. |
| `elaboro_id` | int | FK → `users` (quien elaboró). |
| `estado` | string | Enum, ver §1.4. |
| `tipo` | string | `oportunidad` \| `proyecto`. |
| `ponderacion` | int | % de probabilidad (0,10,25,75,100). Cache del último snapshot. |
| `sublinea_id` | int | FK → `sublineas`. |
| `usuario_final` | string | Cliente final. |
| `sector` | string | |
| `plazo_estimado` | int | Plazo en **meses**. |
| `fecha_inicio_planeada` / `fecha_fin_planeada` | date | |
| `cartera_esperada` | decimal | Calculado: `monto_usd * ponderacion / 100`. |

Relaciones clave: `cliente`, `sublinea`, `lugar`, `cotizaciones` (hasMany),
`miembros` (equipo, hasMany), `historialPonderacion` (hasMany).
Roles de equipo (vía `proyecto_miembros.rol`): `director_dn`, `gerente_proyectos`,
`gerente_operaciones`, `ingeniero_costos`, `ingeniero_proyectos`, `trainee`.

> **Monto de venta:** el monto "real" de una oportunidad sale de la cotización más
> reciente: `cotizaciones.max(precio_venta_final)` o `cotizaciones.sortByDesc(version).first().precio_venta_final`.
> Para el PoC, expón directamente un campo `monto_venta` ya resuelto.

### 1.4 Enum `estado` (catálogo fijo)

```
en_revision           → "En revisión"
cotizando             → "Cotizando"
cotizado              → "Cotizado"
presentado            → "Presentado"
adjudicado_pendiente  → "Adjudicado pend."
adjudicado_firmado    → "Adjudicado"
en_ejecucion          → "En ejecución"
en_cierre             → "En cierre"
cerrado               → "Cerrado"
cancelado             → "Cancelado"
perdido               → "Perdido"
archivado             → "Archivado"
```

Agrupaciones usadas en lógica de negocio:
- **Adjudicados** (cuentan como ganados): `adjudicado_pendiente, adjudicado_firmado, en_ejecucion, en_cierre, cerrado`
- **Pipeline activo** (excluye terminados): todo excepto `cancelado, perdido, archivado`
- **Requieren atención**: `en_revision, cotizando, presentado` sin actividad > 7 días.

### 1.5 Bandas de ponderación

```
0%   → "Perdida"      (perdida)
10%  → "Remoto"
25%  → "Posible"
75%  → "Probable"
100% → "Contratada"
```

### 1.6 `Sublinea` (tabla `sublineas`)

`id`, `codigo`, `nombre`, `descripcion`. Es un catálogo de líneas de negocio usado como
filtro y agrupador.

---

## 2. Pantalla: DASHBOARD (`/`)

**Componente origen:** `App\Livewire\Ejecutivo\DashboardIndex`

### 2.1 Objetivo

Vista ejecutiva del pipeline comercial. Resume en KPIs y gráficas el estado de toda la
cartera de oportunidades: cuánto dinero hay en pipeline, cuánto adjudicado, hit-rate,
concentración de riesgo, carga del equipo y proyección ponderada por mes. Es de
**solo lectura** (analítica), pero con filtros.

### 2.2 Funciones / interacciones

| Función | Descripción | Estado en backend |
|---|---|---|
| Filtro por **periodo** | `mes` \| `trimestre` \| `anio` (default `trimestre`). | `mount` lee `?periodo=`. |
| Filtro por **clientes** | Multiselección de `cliente_id`. | `clientesSeleccionados[]` |
| Filtro por **sublíneas** | Multiselección de `sublinea_id`. | `sublineasSeleccionadas[]` |
| Toggle **incluir SEDENA** | Excluir/incluir cliente que contenga "sedena". | `incluirSedena` bool |
| Ver KPIs | Tarjetas numéricas. | calculado |
| Ver gráficas | Barras apiladas por mes, por sublínea, por cliente; cartera ponderada; evolución; backlog burn-down. | calculado |
| Lista "Oportunidades que requieren atención" | Top 5 estancadas. | calculado |

### 2.3 Datos / contexto que se envía y recibe

**Filtros que el cliente envía** (query params o body):
```json
{
  "periodo": "trimestre",
  "clientes": [1, 4, 7],
  "sublineas": [2],
  "incluir_sedena": true
}
```

### 2.4 Contrato API propuesto

#### `GET /api/v1/dashboard`

Query params: `periodo`, `clientes[]`, `sublineas[]`, `incluir_sedena`.

**Respuesta** (estructura derivada de `getDashboardDataProperty`):

```json
{
  "encabezado": {
    "nombre_usuario": "Oscar Chávez",
    "quarter_label": "Q2 2026"
  },
  "kpis": {
    "pipeline_total": 42,
    "pipeline_monto": 18750000.0,
    "adjudicados_count": 11,
    "adjudicado_monto": 7300000.0,
    "hit_rate_conteo": 26.2,
    "concentracion_sedena": 41.5,
    "meta_adjudicacion": 12400000,
    "meta_pct": 59,
    "dossiers_riesgo": 3,
    "post_mortems": 8
  },
  "carga_equipo": {
    "avg": 4.2,
    "sobrecarga": 2,
    "total": 9
  },
  "distribuciones": {
    "por_sublinea": { "Telecom": 12, "Energía": 8, "Sin línea": 3 },
    "por_estado":   { "cotizando": 9, "presentado": 6, "adjudicado_firmado": 4 },
    "por_cliente":  { "SEDENA": 17, "CFE": 6 }
  },
  "oportunidades_atencion": [
    {
      "id": 88,
      "cp": "CP-2026-014",
      "ref_tecnica": "Radioenlace norte",
      "cliente": { "id": 1, "razon_social": "SEDENA", "alias": "SEDENA" },
      "estado": "cotizando",
      "monto_estimado": 950000.0,
      "dias_sin_actividad": 12,
      "lider": { "id": 5, "name": "Ana López" },
      "accion_sugerida": "Cotizar"
    }
  ]
}
```

> `accion_sugerida` se deriva del estado: `cotizando→"Cotizar"`,
> `presentado→"Presentar"`, otro→`"Dar seguimiento"`.
> `dias_sin_actividad` = días desde `updated_at`.

#### `GET /api/v1/dashboard/charts`

Datos para gráficas (de `getChartDataProperty`). Montos en **millones USD**.

```json
{
  "month_labels": ["Nov 25","Dic 25","Ene 26","...","Dic 26"],
  "by_month": {
    "p100": [0,0,1.2, "..."],
    "p75":  [0,0.5,2.1, "..."],
    "p25":  [1.0,0,0.8, "..."],
    "p10":  [0,0,0.3, "..."]
  },
  "by_sublinea": { "TEL": 8.4, "ENE": 5.1 },
  "by_cliente":  { "SEDENA": 9.2, "CFE": 3.0 }
}
```

#### `GET /api/v1/dashboard/cartera` (Status de Ofertas)

De `getStatusOfertasDataProperty`. Proyección ponderada por mes con evolución histórica
de la ponderación. Útil para gráfica de líneas/área.

```json
{
  "months": ["Ene 26","Feb 26","Mar 26"],
  "cartera_kpis": { "bruto": 18.75, "esperado": 7.30, "count": 42, "eficiencia": 38.9 },
  "cartera_data": {
    "labels": ["0% Perdida","10% Remoto","25% Posible","75% Probable","100% Contratada"],
    "bruto":    [18.7, 16.2, 12.1, 6.0, 2.3],
    "esperado": [7.3, 7.0, 5.1, 3.2, 2.3],
    "counts":   [42, 38, 25, 11, 4]
  },
  "level_resumen": [
    { "key": "p100", "label": "100% Contratada", "bruto": 2.3, "pond": 2.3, "count": 4, "eficiencia": 100.0 }
  ],
  "by_responsable": {
    "labels": ["Ana L", "Carlos M", "Sin asignar"],
    "bruto":  [6.2, 4.1, 1.0],
    "pond":   [3.1, 1.8, 0.2],
    "counts": [8, 5, 2]
  },
  "proyectos": [
    {
      "nombre": "SEDENA Radioenlace norte",
      "cp": "CP-2026-014",
      "monto": 0.95,
      "monto_usd": 950000.0,
      "probs": [25, 75, 75],
      "resp": "Ana L",
      "estado": "cotizando",
      "plazo_meses": 8
    }
  ]
}
```

#### `GET /api/v1/dashboard/backlog` (Meses sin contratación)

De `getMesesSinContratacionDataProperty`. KPI "runway": cuántos meses de trabajo quedan
si no se firma nada nuevo = `backlog_contratado / produccion_mensual`.

```json
{
  "meses": 6.4,
  "backlog_m": 7.3,
  "produccion_mensual_m": 1.14,
  "count_contratados": 11,
  "plazo_promedio": 7,
  "semaforo": { "color": "cyan", "label": "Estable", "desc": "Backlog entre 6 y 9 meses..." },
  "burn_labels": ["Hoy","Mes 1","Mes 2","..."],
  "burn_serie":  [7.3, 6.16, 5.02, "..."],
  "proyectos": [
    { "nombre":"SEDENA","cp":"CP-2026-002","estado":"en_ejecucion","monto_m":2.1,"plazo":12,"mensual_m":0.175 }
  ]
}
```

> **Semáforo del backlog:** `>=9 emerald/Saludable`, `6-9 cyan/Estable`,
> `3-6 amber/En atención`, `>0 red/Crítico`, `0 slate/Sin datos`.

#### Catálogos para los filtros

- `GET /api/v1/clientes?activo=1` → para el multiselect de clientes.
- `GET /api/v1/sublineas` → `[{id, codigo, nombre}]`.

---

## 3. Pantalla: OPORTUNIDADES (`/oportunidades`)

**Componente origen:** `App\Livewire\Proyectos\OportunidadesIndex`

### 3.1 Objetivo

Listado paginado y filtrable del pipeline de oportunidades, con una fila de KPIs arriba.
Es la pantalla operativa del comercial: buscar, filtrar y entrar al detalle de cada
oportunidad. Permiso requerido: `ver oportunidades`.

### 3.2 Funciones / interacciones

| Función | Detalle |
|---|---|
| **Buscar** (`search`) | Busca en `cp_numero`, `dn_numero`, `cliente.razon_social`, `usuario_final` (case-insensitive). |
| **Filtro año** (`anio`) | Default año actual; `"todos"` para no filtrar. |
| **Filtro sublíneas** | Multiselección `sublinea_id`. |
| **Filtro estados** | Multiselección del enum §1.4. |
| **Solo míos** (`soloMios`) | Solo donde el usuario autenticado es `gerente_proyectos`. |
| **Limpiar filtros** | Reset de todos los filtros. |
| **Paginación** | `perPage` configurable (default 25). |
| Orden | Por `fecha_envio DESC` (nulls al final). |

### 3.3 Datos / contexto que se envía

```json
{
  "search": "radioenlace",
  "anio": "2026",
  "sublineas": [2, 5],
  "estados": ["cotizando", "presentado"],
  "solo_mios": false,
  "per_page": 25,
  "page": 1
}
```

### 3.4 Contrato API propuesto

#### `GET /api/v1/oportunidades`

Query: `search`, `anio`, `sublineas[]`, `estados[]`, `solo_mios`, `per_page`, `page`.

```json
{
  "data": [
    {
      "id": 88,
      "cp_numero": "CP-2026-014",
      "dn_numero": "DN-014",
      "tech_reference": "Radioenlace norte",
      "anio": 2026,
      "cliente": { "id": 1, "razon_social": "SEDENA", "alias": "SEDENA" },
      "sublinea": { "id": 2, "codigo": "TEL", "nombre": "Telecom" },
      "lugar": { "id": 3, "nombre": "Sonora" },
      "estado": "cotizando",
      "estado_label": "Cotizando",
      "ponderacion": 25,
      "monto_usd": 950000.0,
      "monto_venta": 950000.0,
      "cartera_esperada": 237500.0,
      "fecha_envio": "2026-05-30",
      "gerente_proyectos": { "id": 5, "name": "Ana López" },
      "elaboro": { "id": 9, "name": "Carlos M" }
    }
  ],
  "meta": { "current_page": 1, "last_page": 4, "total": 87, "per_page": 25 }
}
```

#### `GET /api/v1/oportunidades/kpis`

De `getKpisProperty`. Respeta el filtro `anio`. Excluye `cancelado, perdido, archivado`.

```json
{
  "pipeline_monto": 18750000.0,
  "pipeline_count": 42,
  "monto_ponderado": 7300000.0,
  "ponderacion_media": 38,
  "adjudicado_monto": 7300000.0,
  "adjudicado_count": 11,
  "enviadas_count": 28
}
```

> `monto_ponderado = SUM(monto_usd * ponderacion / 100)`.
> `adjudicado` aquí = estados `adjudicado_pendiente, adjudicado_firmado, en_ejecucion`.
> `enviadas` = pipeline excepto `en_revision, cotizando`.

#### `GET /api/v1/oportunidades/{id}` (detalle, para navegación)

Devuelve el `Proyecto` completo con relaciones (`cliente`, `sublinea`, `lugar`,
`miembros` con roles, `cotizaciones`, `historialPonderacion`). Para el PoC basta con los
campos de §1.3 + equipo + última cotización.

---

## 4. Pantalla: CLIENTES (`/clientes`)

**Componentes origen:** `App\Livewire\Comercial\ClientesIndex` (listado + alta),
`App\Livewire\Comercial\ClienteDetalle` (detalle), `ClienteController` (CRUD REST-ish).

### 4.1 Objetivo

Gestión de la cartera de clientes: listar/buscar, crear, editar, agregar contactos y
desactivar. Es **CRUD** (a diferencia de las otras dos pantallas). Permiso de lista:
`ver proyectos`; crear/editar/eliminar vía políticas (`create`/`update`/`delete` sobre `Cliente`).

### 4.2 Funciones / interacciones

| Función | Detalle |
|---|---|
| **Buscar** (`buscar`) | En `razon_social`, `alias`, `rfc` (case-insensitive). |
| **Filtro sector** | Igualdad exacta sobre `sector`. |
| **Incluir inactivos** | Toggle; por defecto solo `activo: true`. |
| **Crear cliente** | Modal. Valida `razon_social` req, `alias` req+único (→MAYÚS), `rfc` único opcional. |
| **Editar cliente** | (detalle) razón social, alias, rfc, sector, segmento. |
| **Agregar contacto** | nombre req, puesto/email/teléfono opc, `principal` (desmarca los otros). |
| **Eliminar** | Soft: pone `activo: false`. |
| Paginación | 25 por página, orden por `razon_social`. |
| **Detalle** | Muestra contactos + proyectos del cliente + KPIs (facturado, pipeline activo). |

### 4.3 Datos / contexto

Alta de cliente (lo que envía el cliente):
```json
{ "razon_social": "Comisión Federal X", "alias": "CFE-X", "rfc": "CFE920101AAA", "sector": "Energía", "segmento": "Pública" }
```

Alta de contacto:
```json
{ "nombre": "Juan Pérez", "puesto": "Director de compras", "email": "j@cfe.mx", "telefono": "555-1234", "principal": true }
```

### 4.4 Contrato API propuesto

#### `GET /api/v1/clientes`

Query: `buscar`, `sector`, `incluir_inactivos`, `page`.

```json
{
  "data": [
    {
      "id": 1,
      "razon_social": "SEDENA",
      "alias": "SEDENA",
      "rfc": null,
      "sector": "Defensa",
      "segmento": "Gobierno",
      "activo": true,
      "contactos_count": 3
    }
  ],
  "meta": { "current_page": 1, "last_page": 2, "total": 35, "per_page": 25 }
}
```

#### `GET /api/v1/clientes/sectores`

`["Defensa", "Energía", "Telecom"]` — para el dropdown de filtro.

#### `GET /api/v1/clientes/{id}` (detalle)

De `ClienteDetalle::render`:

```json
{
  "cliente": {
    "id": 1, "razon_social": "SEDENA", "alias": "SEDENA",
    "rfc": null, "sector": "Defensa", "segmento": "Gobierno", "activo": true,
    "contactos": [
      { "id": 7, "nombre": "Juan Pérez", "puesto": "Director", "email": "j@x.mx", "telefono": "555", "principal": true }
    ]
  },
  "kpis": {
    "facturado_total": 4200000.0,
    "pipeline_activo": 1850000.0
  },
  "oportunidades": [
    { "id": 88, "tech_reference": "Radioenlace", "estado": "cotizando", "monto_venta": 950000.0, "sublinea": {"codigo":"TEL"} }
  ],
  "proyectos": [
    { "id": 90, "tech_reference": "Planta X", "estado": "en_ejecucion", "monto_venta": 2100000.0 }
  ]
}
```

> `facturado_total` = suma de `cotizaciones.max(precio_venta_final)` de proyectos en
> `cerrado, en_cierre, adjudicado_firmado, en_ejecucion`.
> `pipeline_activo` = suma en `cotizando, cotizado, presentado`.
> `oportunidades` excluye estados terminados (`cancelado, perdido, archivado,
> en_ejecucion, en_cierre, cerrado`).

#### `POST /api/v1/clientes` (crear)

Body = §4.3 alta. Validación: `razon_social` required|max:255, `alias`
required|max:30|unique, `rfc` nullable|max:13|unique, `sector`/`segmento` nullable|max:100.
`alias` y `rfc` se guardan en MAYÚSCULAS. → `201` con el cliente creado.

#### `PUT /api/v1/clientes/{id}` (editar)

Mismos campos + `activo`. `alias`/`rfc` únicos ignorando el propio id.

#### `POST /api/v1/clientes/{id}/contactos` (agregar contacto)

Body = §4.3 contacto. Si `principal: true`, desmarca los demás. → `201`.

#### `DELETE /api/v1/clientes/{id}` (desactivar)

Soft delete → `activo: false`. → `200`.

---

## 5. Autenticación (para fase API real)

La web usa Auth0/Google (`Auth0Controller`) y email/password (`LoginController`).
Para móvil se recomienda:

- **Login:** `POST /api/v1/auth/login { email, password }` → `{ token, user }` (Sanctum),
  o flujo OAuth2/PKCE contra Auth0 si se quiere SSO.
- Enviar `Authorization: Bearer {token}` en todas las llamadas.
- `GET /api/v1/me` → datos del usuario y permisos (`ver dashboard`, `ver oportunidades`,
  `ver proyectos`, etc.) para ocultar/mostrar pantallas.

**Para el PoC con mock:** simular un usuario fijo y saltar el login, o una pantalla de
login que acepte cualquier credencial y devuelva un token fake.

---

## 6. (Opcional) Buscador inteligente — único endpoint JSON que ya existe

Hoy ya funciona en la web y se puede portar tal cual. Es un proxy a un servicio
externo de IA de consultas a datos.

- `POST /consulta-ia` body `{ consulta, origen?, formato?, objetivo? }` →
  objeto estructurado `{ tipo, texto, tabla, grafico, meta }`.
- `POST /consulta-ia/voz` multipart con `file` (audio, máx 25MB).
- `GET /consulta-ia/health` → estado del servicio.

Útil si el PoC quiere una barra de "pregúntale a tus datos".

---

## 7. Sugerencia de stack y estructura Flutter para el PoC

> No es obligatorio, pero acelera al agente.

- **Estado:** Riverpod o Bloc.
- **HTTP:** Dio (con interceptor que inyecte el Bearer y, en modo mock, devuelva los
  JSON de este doc).
- **Modelos:** `freezed` + `json_serializable`, generados desde los contratos de §2–§4.
- **Navegación:** `go_router` con 3 tabs (Dashboard, Oportunidades, Clientes) + rutas de
  detalle (`/oportunidades/:id`, `/clientes/:id`).
- **Gráficas:** `fl_chart` (barras apiladas, líneas, donut) para reproducir las del dashboard.
- **Modo mock:** carpeta `assets/mocks/*.json` con un response por endpoint; un flag
  `USE_MOCK=true` que el repositorio de datos respeta.

### Mapa pantalla → endpoints

| Pantalla Flutter | Endpoints |
|---|---|
| Dashboard | `GET /dashboard`, `/dashboard/charts`, `/dashboard/cartera`, `/dashboard/backlog`, `/clientes`, `/sublineas` |
| Oportunidades (lista) | `GET /oportunidades`, `/oportunidades/kpis`, `/sublineas` |
| Oportunidad (detalle) | `GET /oportunidades/{id}` |
| Clientes (lista) | `GET /clientes`, `/clientes/sectores` |
| Cliente (detalle) | `GET /clientes/{id}`, `PUT /clientes/{id}`, `POST /clientes/{id}/contactos` |
| Cliente (alta) | `POST /clientes` |

---

## 8. Checklist de implementación del PoC

- [ ] Definir modelos Dart (`Cliente`, `Contacto`, `Proyecto`/`Oportunidad`, `Sublinea`, KPIs).
- [ ] Capa de datos con repositorios + modo mock conmutable.
- [ ] Mocks JSON para cada endpoint (usar los ejemplos de este doc).
- [ ] Tab Dashboard: KPIs + 3-4 gráficas + lista "requieren atención".
- [ ] Tab Oportunidades: KPIs + lista filtrable/paginada + buscador.
- [ ] Tab Clientes: lista filtrable + alta (modal) + detalle con contactos y proyectos.
- [ ] Navegación a detalles.
- [ ] (Opcional) Login fake + manejo de Bearer token.
```
