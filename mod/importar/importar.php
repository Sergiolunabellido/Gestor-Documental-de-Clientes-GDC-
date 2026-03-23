<div class="vh-100 w-full d-flex flex-column aling-items-start ">
    <div id="cabeceraImportar" class="d-flex align-items-center justify-content-between m-3">
        <div class="d-flex flex-wrap align-items-center w-100 gap-3">
            <label for="listaClientes">Seleccione al cliente:</label>
            <select name="listaClientes" id="listaClientes" required class="w-sm-25 w-25 rounded shadow-sm">

            </select>
            <input id="prefijoTabla" type="text" class="rounded " placeholder="Prefijo para la tabla">
            <input id="dbDestino" type="text" class="rounded" placeholder="DB destino">
           
        </div>
        <button id="exportarFicherosCliente" class="btn btn-primary">Exportar</button>
    </div>
    <div id="barraProgresiva" class="progress visually-hidden mx-3" aria-label="Progreso de exportación">
        <div id="barraProgresivaBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
    </div>
    <div id="tablaArchivosClientes"  class="m-3 h-100 border border-2 shadow rounded overflow-auto"
        style="max-height: calc(100vh - 220px);">
    </div>
</div>
