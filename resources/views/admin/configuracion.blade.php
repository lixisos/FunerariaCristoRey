<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-indigo-950">Configuración Institucional del Sistema</h2>
            <p class="text-xs text-slate-500">Parámetros operativos vigentes de Funeraria Cristo Rey — Temuco</p>
        </div>
    </x-slot>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <div class="space-y-2">
            <h3 class="font-bold text-sm text-indigo-950 border-b pb-2">Datos Casa Matriz</h3>
            <p><strong>Razón Social:</strong> Servicios Funerarios Cristo Rey SpA</p>
            <p><strong>Dirección Salón de Ventas:</strong> Calle Manuel Blanco #412, Temuco, Región de La Araucanía</p>
            <p><strong>Teléfono Turno 24/7:</strong> +56 9 8765 4321</p>
        </div>
        <div class="space-y-2">
            <h3 class="font-bold text-sm text-indigo-950 border-b pb-2">Reglas de Negocio Activas</h3>
            <p><strong>Capacidad Máxima Salón Calle Blanco:</strong> 24 urnas en exhibición</p>
            <p><strong>Lote Mínimo Fábrica Santiago:</strong> 24 unidades por camión</p>
            <p><strong>Umbral Alerta Stock Crítico:</strong> &le; 2 unidades totales</p>
            <p><strong>Semáforo Legal Seremi:</strong> Alerta roja &lt; 3 horas antes del límite de 48 hrs</p>
        </div>
    </div>
</x-app-layout>