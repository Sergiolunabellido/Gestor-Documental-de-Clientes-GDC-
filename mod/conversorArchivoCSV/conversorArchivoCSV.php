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
    <div id="divPadreFicheros" class="d-flex align-items-center gap-5 m-5 h-100 w-90" style="max-height: 100vh;">
        <div id="expresionCSV" class="d-flex flex-column align-items-center justify-content-center gap-5 w-50 h-100 border-end">
            <Label for="expresionCSV" class="fs-1">Expresion</Label>

            <div class="import-select w-50">
                <?php
                    $o = new CSVImportar();
                    $o->setFile("assets/archivosC/cliente_40/archivo1.csv"); // Usando uno de los CSV que creamos
                    $o->setClass("mi-clase");
                    $id = $o->getId();

                    $o->render();
                ?>
            </div>
    
        </div>
        <div id="tablaCampos" class="d-flex flex-column align-items-center justify-content-center gap-5 w-50 h-100">
            <Label class="fs-1">Tabla</Label>

            <input list="camposTabla" class="form-select w-50 rounded shadow" placeholder="Selecciona o escribe">

            <datalist id="camposTabla">
                <option value="nombre">
                <option value="apellido">
                <option value="telefono">
            </datalist>
        </div>
    </div>

</div>
