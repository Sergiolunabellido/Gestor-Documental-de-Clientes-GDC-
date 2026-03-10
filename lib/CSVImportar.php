<?php

/**
 * @brief Gestiona la lectura y renderizado de columnas de un CSV para el modulo de importacion
 * Fecha de creacion: 2026-03-09
 */
class CSVImportar {

    private PDO $db;

    private $file;
    private $columnas = [];
    private $datos = [];
    private $id;
    private $class;
    private $delimiter = ',';

    /**
     * @brief Constructor de la clase CSVImportar
     * Fecha de creacion: 2026-03-09
     * @return void
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * @brief Define la ruta del archivo CSV a procesar
     * Fecha de creacion: 2026-03-09
     * @param string $path Ruta relativa o absoluta del CSV
     * @return void
     */
    public function setFile($path) {
        $this->file = $path;
    }

    /**
     * @brief Define la clase CSS principal del componente renderizado
     * Fecha de creacion: 2026-03-09
     * @param string $className Nombre de clase CSS
     * @return void
     */
    public function setClass($className) {
        $this->class = $className;
    }

    /**
     * @brief Genera y devuelve un identificador unico para el componente
     * Fecha de creacion: 2026-03-09
     * @return string ID unico del componente CSV
     */
    public function getId() {
        // Generamos un ID único para que el JS sepa dónde actuar
        $this->id = "import_" . uniqid();
        return $this->id;
    }

    /**
     * @brief Elimina el BOM UTF-8 del inicio de un valor de texto
     * Fecha de creacion: 2026-03-09
     * @param mixed $valor Valor de entrada a normalizar
     * @return mixed Valor sin BOM si era texto
     */
    private function limpiarBOM($valor) {
        if (!is_string($valor)) {
            return $valor;
        }
        return preg_replace('/^\xEF\xBB\xBF/', '', $valor);
    }

    /**
     * @brief Detecta automaticamente el delimitador usado por el archivo CSV
     * Fecha de creacion: 2026-03-09
     * @return string Delimitador detectado
     */
    private function detectarSeparador() {
        if (empty($this->file) || !is_readable($this->file)) {
            return $this->delimiter;
        }

        $linea = '';
        $f = fopen($this->file, 'r');
        if ($f === false) {
            return $this->delimiter;
        }

        while (($raw = fgets($f)) !== false) {
            $raw = trim($raw);
            if ($raw !== '') {
                $linea = $this->limpiarBOM($raw);
                break;
            }
        }
        fclose($f);

        if ($linea === '') {
            return $this->delimiter;
        }

        $candidatos = [',', ';', "\t", '|'];
        $mejor = $this->delimiter;
        $maxCampos = 1;
        $maxSeparadores = 0;

        foreach ($candidatos as $candidato) {
            $campos = str_getcsv($linea, $candidato);
            $cantidadCampos = is_array($campos) ? count($campos) : 1;
            $cantidadSeparadores = substr_count($linea, $candidato);

            if (
                $cantidadCampos > $maxCampos ||
                ($cantidadCampos === $maxCampos && $cantidadSeparadores > $maxSeparadores)
            ) {
                $mejor = $candidato;
                $maxCampos = $cantidadCampos;
                $maxSeparadores = $cantidadSeparadores;
            }
        }

        $this->delimiter = $mejor;
        return $this->delimiter;
    }

    /**
     * @brief Determina si la primera fila del CSV debe tratarse como cabecera
     * Fecha de creacion: 2026-03-09
     * @return bool True si se detecta cabecera, false en caso contrario
     */
    public function detectarCabezera() {
        if (empty($this->file) || !is_readable($this->file)) {
            return false;
        }

        $delimiter = $this->detectarSeparador();

        $f = fopen($this->file, "r");
        if ($f === false) {
            return false;
        }

        $filas = [];

        for ($i = 0; $i < 5; $i++) {
            $fila = fgetcsv($f, 0, $delimiter);
            if ($fila === false) break;
            if ($i === 0 && isset($fila[0])) {
                $fila[0] = $this->limpiarBOM($fila[0]);
            }
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

    /**
     * @brief Carga la primera fila del CSV en la propiedad de columnas
     * Fecha de creacion: 2026-03-09
     * @return void
     */
    public function cargarCabeceras() {
        $delimiter = $this->detectarSeparador();
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            $this->columnas = fgetcsv($handle, 0, $delimiter);
            if (isset($this->columnas[0])) {
                $this->columnas[0] = $this->limpiarBOM($this->columnas[0]);
            }
            fclose($handle);
        }
    }

    /**
     * @brief Renderiza el componente HTML con columnas detectadas del CSV
     * Fecha de creacion: 2026-03-09
     * @return void
     */
    public function renderCSV() {

        // Leemos la primera fila del CSV para mostrar las cabeceras al usuario

        $tieneCabezera = $this->detectarCabezera();

        $headers = [];
        if($tieneCabezera) {
            $this->cargarCabeceras();
            $headers = $this->columnas;
        } else {
            $previewData = $this->previewCSV();
            // Si no hay cabecera, generamos nombres genéricos
            $delimiter = $this->detectarSeparador();
            $f = fopen($this->file, "r");
            $fila = fgetcsv($f, 0, $delimiter);
            fclose($f);
            for ($i = 0; $i < count($fila); $i++) {
                $headers[] = "Campo " . ($i + 1)." (".$previewData[$i].")";
            }
        }

        // Renderizado del componente (HTML + Data para JS)
        echo "<div id='{$this->id}' class='{$this->class}' data-headers='".json_encode($headers)."'>";
        echo "  <div class='mapping-container flex flex-column gap-5' >";
        echo "      <div class='expressions csv-inputs-container d-flex flex-column gap-2' data-headers='".json_encode($headers)."'>";
        echo "          <div class='fila-expresion d-flex align-items-center justify-content-center gap-3 w-100' data-id='1'>";
        echo "              <input list='camposCSV-{$this->id}' id='expresion_1' class='form-control w-75 rounded shadow' placeholder='Selecciona o escribe' data-can-add='true'>";
        echo "              <button class='btn botonEliminarExpresion' title='Eliminar expresión'> - </button>";
        echo "              <button class='btn botonAñadirExpresion' title='Añadir expresión'> + </button>";
        echo "              <p class='visually-hidden'>{$this->id}<p/>    ";
        echo "          </div>";
        echo "          <datalist id='camposCSV-{$this->id}'>";
        foreach ($headers as $header) {
            echo "<option value='{$header}'>{$header}</option>";
        }
        echo "          </datalist>";
        echo "      </div>";
        echo "  </div>";
        echo "</div>";
    }
    
    /**
     * @brief Lee la primera fila del CSV para mostrar una vista previa al usuario cuando no se detecta cabecera
     * Fecha de creacion: 2026-03-10
     * @return array Primera fila del CSV como vista previa
     */
    public function previewCSV() {

        // Array para almacenar la primera fila del CSV
        $previewData = [];
        $delimiter = $this->detectarSeparador();
        // Abrimos el archivo CSV para lectura
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            // Leemos la primera fila del CSV
            $fila = fgetcsv($handle, 0, $delimiter);
            if ($fila !== FALSE) {
                // Limpiamos el BOM de la primera celda si es necesario
                if (isset($fila[0])) {
                    $fila[0] = $this->limpiarBOM($fila[0]); 
                }
                $previewData = $fila;
            }
            fclose($handle);
        }

        return $previewData;

    }
}
