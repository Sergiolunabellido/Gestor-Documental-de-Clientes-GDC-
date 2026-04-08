<div class="vh-100 w-75 d-flex flex-column aling-items-start " id="archivos">
    <div class="m-3 d-flex justify-content-between align-items-center w-full">
        <button id="botonAñadirFichero" class="btn btn-primary">
            Subir fichero
        </button>
        <h1 id="nombreFicheroSubido"></h1>
        <button id="botonEliminarFichero" class="btn btn-danger">
            Eliminar fichero
        </button>
    </div>
    <div id="divPadreFicheros" class="m-5" style="max-height: 100vh; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; align-content: start;">

    </div>
</div>

<div class="vh-100 w-full d-none flex-column aling-items-start  " id="detalleTabla">
    <div id="detalleTablaTitulo" class="m-3 w-full gap-4 d-flex align-items-center ">
        <button id="botonVolverArchivo" class="btn h-10  btn-dark">
            <h2> < </h2>
        </button>
        <div class="w-100 border border-2 shadow rounded d-flex align-items-center justify-content-center">
        
            <div id="contenidoTabla" class="m-3">
                <h2 id="nombreTabla"></h2>
            </div>
        </div>
    </div>
    
   
    <div
        id="datosTabla"
        class="m-3 h-100 border border-2 shadow rounded overflow-auto "
        style="max-height: calc(100vh - 220px);"
    >
       
    </div>
</div>
