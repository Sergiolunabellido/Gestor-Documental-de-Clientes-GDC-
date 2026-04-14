<div class="vh-100 w-75 d-flex flex-column align-items-center ">
    
    <div class="d-flex flex-column align-items-center m-3">
        <h1>Lista de usuarios</h1>
        <div class="d-flex align-items-center justify-content-center gap-3">
            <input type="text" placeholder="Nombre Usuario" id="nombreUserFiltro" class="form-control">
            <input type="text" placeholder="Correo Usuario" id="correoUserFiltro" class="form-control">
            <input type="text" placeholder="Fecha Creacion(YYYY-MM-DD)" id="fechaUserFiltro" class="form-control">
            <button type="button" id="botonFiltrar" class="btn btn-primary">Filtrar</button>
        </div>
    </div>
    <div id="divs-usuarios" class="w-75 h-100" style="max-height: calc(100vh - 220px); overflow-y: auto; overflow-x: hidden;">
       
    </div>
</div>



