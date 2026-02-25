<div class="vh-100 w-full d-flex flex-column aling-items-start  ">
    
    <div class="m-3 w-full  border border-2 shadow rounded d-flex align-items-center justify-content-between">
        <div id="contenidoClienteDetalle" class="m-3">
            <h2 id="nombreClienteDetalle"></h2>
            <p id="telefonoClienteDetalle"></p>
        </div>
       
    </div>
    <div class="d-flex align-items-center justify-content-between gap-2">
    
        <input id="inputArchivo"  type="file"  hidden multiple>
        <label for="inputArchivo" class="w-25 ms-3 btn btn-primary ">
            Subir el archivo
        </label>


        <button id="botonEliminarTodos" class="btn btn-danger w-25 me-3">Eliminar Todos</button>

    </div>
   
    <div id="ficherosCliente" class="m-3 h-100 border border-2 shadow rounded overflow-auto"
    style="max-height: calc(100vh - 220px);">
       
    </div>
</div>