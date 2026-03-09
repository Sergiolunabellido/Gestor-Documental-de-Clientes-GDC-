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
        if (empty($this->file) || !is_readable($this->file)) {
            return false;
        }

        $f = fopen($this->file, "r");
        if ($f === false) {
            return false;
        }

        $filas = [];

        for ($i = 0; $i < 5; $i++) {
            $fila = fgetcsv($f);
            if ($fila === false) break;
            $filas[] = $fila;
        }

        fclose($f);

        if (count($filas) < 2 || !is_array($filas[0]) || count($filas[0]) === 0) {
            return false;
        }

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

        // Si al menos una columna parece encabezado (texto en fila 1 y números debajo),
        // tratamos la primera fila como cabecera.
        return $sospechosas >= 1;

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
        if($tieneCabezera) {
            $this->cargarCabeceras();
            $headers = $this->columnas;
        } else {
            // Si no hay cabecera, generamos nombres genéricos
            $f = fopen($this->file, "r");
            $fila = fgetcsv($f);
            fclose($f);
            for ($i = 0; $i < count($fila); $i++) {
                $headers[] = "Campo " . ($i + 1);
            }
        }
        // Renderizado del componente (HTML + Data para JS)
        echo "<div id='{$this->id}' class='{$this->class}' data-headers='".json_encode($headers)."'>";
        echo "  <div class='mapping-container flex flex-column gap-5' >";
        echo "      <div class='expressions fs-1'>";
        echo "          <div id='exp-list-{$this->id}'></div>";
        echo "      </div>";
        echo "      <div class='destination'>";
        echo "          <input list='camposCSV' class='form-select w-100 shadow' placeholder='Selecciona o escribe'>";
        echo "          <datalist  id='camposCSV'>";
        foreach ($headers as $header) {
            echo "<option value='{$header}'>{$header}</option>";
        }
        echo "          </datalist>";

        echo "      </div>";
        echo "  </div>";
        echo "</div>";
    }
}
