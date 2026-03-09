<div class="vh-100 w-full d-flex flex-column aling-items-start ">
    <div id="cabeceraImportar" class="d-flex align-items-center justify-content-between m-3">
        <div class="d-flex flex-wrap align-items-center w-50 gap-3">
            <label for="listaClientes">Seleccione al cliente:</label>
            <select name="listaClientes" id="listaClientes" required class="w-sm-25 w-50">

            </select>
        </div>
        <button id="exportarFicherosCliente" class="btn btn-primary">Exportar</button>
    </div>
    <div id="tablaArchivosClientes"  class="m-3 h-100 border border-2 shadow rounded overflow-auto"
        style="max-height: calc(100vh - 220px);">

    </div>
</div>
