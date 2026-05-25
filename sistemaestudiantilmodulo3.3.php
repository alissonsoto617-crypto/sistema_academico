<?php
class clasesistema
{
    private $host = "localhost";
    private $usuario = "root";
    private $password = "chilaquilas25";
    private $bd = "nextgen_db";
    private $puerto = 3308;
    private $conexion;

    public function __construct()
    {
        $this->conexion = new mysqli(
            $this->host,
            $this->usuario,
            $this->password,
            $this->bd,
            $this->puerto
        );

        if ($this->conexion->connect_error)
        {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
    }

    // INSERTAR
    public function insertarGenerico($tabla, $datos)
    {
        $campos = implode(",", array_keys($datos));
        $valores = "'" . implode("','", array_map([$this->conexion, 'real_escape_string'], array_values($datos))) . "'";

        $sql = "INSERT INTO $tabla ($campos) VALUES ($valores)";

        if (!$this->conexion->query($sql))
        {
            die("Error en INSERT: " . $this->conexion->error);
        }

        return true;
    }

    // OBTENER TODOS
    public function obtenerGenerico($tabla)
    {
        $sql = "SELECT * FROM $tabla";
        return $this->conexion->query($sql);
    }

    // OBTENER POR ID
    public function seleccionarGenerico($tabla, $campoId, $id)
    {
        $id = $this->conexion->real_escape_string($id);

        $sql = "SELECT * FROM $tabla WHERE $campoId = '$id'";
        $resultado = $this->conexion->query($sql);

        return $resultado->fetch_assoc();
    }

    // ELIMINAR
    public function eliminarGenerico($tabla, $campoId, $id)
    {
        $id = $this->conexion->real_escape_string($id);

        $sql = "DELETE FROM $tabla WHERE $campoId = '$id'";
        return $this->conexion->query($sql);
    }

    // ACTUALIZAR
    public function actualizarGenerico($tabla, $datos, $campoId, $id)
    {
        $set = "";

        foreach ($datos as $campo => $valor)
        {
            $valor = $this->conexion->real_escape_string($valor);
            $set .= "$campo='$valor',";
        }

        $set = rtrim($set, ",");

        $id = $this->conexion->real_escape_string($id);

        $sql = "UPDATE $tabla SET $set WHERE $campoId='$id'";

        if (!$this->conexion->query($sql))
        {
            die("Error en UPDATE: " . $this->conexion->error);
        }

        return true;
    }
    public function ejecutarConsulta($sql)
{
    $resultado = $this->conexion->query($sql);

    if(!$resultado){
        die("Error en CONSULTA: " . $this->conexion->error);
    }

    return $resultado;
}
}
?>