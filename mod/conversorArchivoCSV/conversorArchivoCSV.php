<div class="vh-100 w-full d-flex flex-column aling-items-start ">
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
