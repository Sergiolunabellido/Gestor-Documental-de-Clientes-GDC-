<?php

class CSVImportar {

    private $file;
    private $columnas = [];
    private $datos = [];
    private $id;
    private $class;

    public function __construct() {

    }

    public function setFile($path) {
        $this->file = $path;
    }

    public function setClass($className) {
        $this->class = $className;
    }

    public function getId() {
        // Generamos un ID único para que el JS sepa dónde actuar
        $this->id = "import_" . uniqid();
        return $this->id;
    }

    public function detectarCabezera() {

        $f = fopen($this->file, "r");

        $filas = [];

        for ($i = 0; $i < 5; $i++) {
            $fila = fgetcsv($f);
            if ($fila === false) break;
            $filas[] = $fila;
        }

        fclose($f);

        $colCount = count($filas[0]);
        $sospechosas = 0;

        for ($c = 0; $c < $colCount; $c++) {

            $numericos = 0;

            for ($i = 1; $i < count($filas); $i++) {
                if (is_numeric($filas[$i][$c])) {
                    $numericos++;
                }
            }

            if ($numericos > 0 && !is_numeric($filas[0][$c])) {
                $sospechosas++;
            }
        }

        return $sospechosas > ($colCount / 2);

    }

    public function cargarCabeceras() {
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            $this->columnas = fgetcsv($handle, 1000, ",");
            fclose($handle);
        }
    }

    public function render() {
        // Leemos la primera fila del CSV para mostrar las cabeceras al usuario

        $tieneCabezera = $this->detectarCabezera();

        $headers = [];
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ",");
            fclose($handle);
        }

        // Renderizado del componente (HTML + Data para JS)
        echo "<div id='{$this->id}' class='{$this->class}' data-headers='".json_encode($headers)."'>";
        echo "  <div class='mapping-container flex flex-column gap-5' >";
        echo "      <div class='expressions fs-1'><h4>Expresiones</h4><div id='exp-list-{$this->id}'></div></div>";
        echo "      <div class='destination'><select class='db-table w-100 rounded shadow' id='camposCSV'><option>Cargando...</option></select></div>";
        echo "  </div>";
        echo "</div>";
    }
}