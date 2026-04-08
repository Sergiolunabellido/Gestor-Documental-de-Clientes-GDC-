<div id="pantallaImportar" class="vh-100 w-75 d-flex flex-column aling-items-start ">
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
        <div id="barraProgresivaBar" class="progress-bar progress-bar-striped progress-bar-animated " role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
    </div>
    <div id="tablaArchivosClientes"  class="m-3 h-100 border border-2 shadow rounded overflow-auto"
        style="max-height: calc(100vh - 220px);">
    </div>
</div>


<div id="conversorArchivo" class="vh-100 w-full d-none flex-column aling-items-start ">
    <div id="cabeceraConversor" class="d-flex align-items-center justify-content-between w-full  m-3 ">
        <button id="botonVolver" class="btn  btn-dark">
            <h2> < </h2>
        </button>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <h3 id="nombreCliente" class="text-uppercase"></h3>
            -
            <h3 id="nombreArchivo"></h3>
        </div>
       
        <button id="botonGuardar" class="btn btn-primary">
            Guardar
        </button>
    </div>
    <div id="divPadreFicheros" class="d-flex flex-column align-items-center gap-5 m-5 h-100 w-90" style="max-height: 100vh;">
        <div class="d-flex align-items-center justify-content-around gap-5 m-5 w-100">
            <Label for="expresionCSV" class="fs-1 w-50 h-100 text-center">Expresion</Label>
            <div class="d-flex flex-column fs-1 w-50 text-center h-100">
                <Label >Tabla</Label>
                <div id="divDatosCSV" class="d-flex flex-column gap-3 align-items-center w-100">
                    <label for="tablas">Elige la tabla de ejemplo:</label>
                    <select name="tablas" id="tablas" class="form-select w-50 rounded shadow" >
                        <option>Selecciona cualquier tabla:</option>
                    </select>
                </div>
            </div>
            
        </div>
        <div class="d-flex align-items-center gap-5 m-5 h-100 w-100">
            <div id="expresionCSV" class="d-flex flex-column align-items-center justify-content-center gap-5 w-50 h-100 ">
                <div class="import-select w-50" id="contenedorExpresion">
                
                </div>
            </div>
            <div id="tablaCampos" class="d-flex flex-column align-items-center justify-content-center gap-5 w-50 h-100">

                <div id="divCamposTabla" class="d-flex flex-column align-items-center justify-content-center gap-3 w-100">
                    <div class="fila-campo d-flex align-items-center justify-content-center gap-3 w-100" data-id="1">
                        <select id="camposTabla_1" class="camposTabla form-select w-50 rounded shadow" placeholder="Seleccion el campo">
                        </select>
                        <button class="btn botonEliminarCampo" title="Eliminar campo"> - </button>
                        <button class="btn botonAñadirCampo" title="Añadir campo"> + </button>
                    </div>
                </div>
                
            </div>
        </div>
       
    </div>

</div>
