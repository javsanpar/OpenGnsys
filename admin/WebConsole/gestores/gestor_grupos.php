<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_grupos.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de grupos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("./relaciones/aulas_eliminacion.php");
include_once("./relaciones/ordenadores_eliminacion.php");
include_once("./relaciones/gruposordenadores_eliminacion.php");
include_once("./relaciones/procedimientos_eliminacion.php");
include_once("./relaciones/tareas_eliminacion.php");
include_once("./relaciones/trabajos_eliminacion.php");
include_once("./relaciones/imagenes_eliminacion.php");
include_once("./relaciones/hardwares_eliminacion.php");
include_once("./relaciones/perfileshard_eliminacion.php");
include_once("./relaciones/softwares_eliminacion.php");
include_once("./relaciones/perfilessoft_eliminacion.php");
include_once("./relaciones/incrementales_eliminacion.php");
include_once("./relaciones/repositorios_eliminacion.php");
include_once("./relaciones/menus_eliminacion.php");
include_once("./relaciones/reservas_eliminacion.php");
include_once("./relaciones/entidades_eliminacion.php");
include_once("./relaciones/centros_eliminacion.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$nombregrupo=""; 
$grupoid=0; 
$idgrupo=0; 
$tipo=0; 
$literaltipo=""; 
$iduniversidad=0; 
$comentarios="";

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros
if (isset($_POST["nombregrupo"])) $nombregrupo=$_POST["nombregrupo"];
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["idgrupo"])) $idgrupo=$_POST["idgrupo"];
if (isset($_POST["tipo"])) $tipo=$_POST["tipo"];
if (isset($_POST["literaltipo"])) $literaltipo=$_POST["literaltipo"];
if (isset($_POST["iduniversidad"])) $iduniversidad=$_POST["iduniversidad"];
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"];

$tablanodo=""; // Arbol para nodos insertados

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
// *************************************************************************************************************************************************
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<BODY>
	<SCRIPT language="javascript" src="../jscripts/propiedades_grupos.js"></SCRIPT>
<?php
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_grupos";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_grupos";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_grupos";
			break;
		default:
			break;
	}
echo '<p><span id="arbol_nodo">'.$tablanodo.'</span></p>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var o=document.getElementById("arbol_nodo");'.chr(13);
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idgrupo.",o.innerHTML);";
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$nombregrupo."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idgrupo.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?php
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla grupos
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;
	global	$idcentro;
	global	$nombregrupo;
	global	$grupoid;
	global	$idgrupo;
	global	$tipo;
	global	$literaltipo;
	global	$iduniversidad;
	global 	$comentarios;
	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$tablanodo;

	if($iduniversidad) $idcentro=0; // Administración 

	$cmd->CreaParametro("@nombregrupo",$nombregrupo,0);
	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idgrupo",$idgrupo,1);
	$cmd->CreaParametro("@tipo",$tipo,1);
	$cmd->CreaParametro("@iduniversidad",$iduniversidad,1);
	$cmd->CreaParametro("@comentarios",$comentarios,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO grupos(nombregrupo,idcentro,grupoid,tipo,iduniversidad,comentarios) VALUES (@nombregrupo,@idcentro,@grupoid,@tipo,@iduniversidad,@comentarios)";
			$resul=$cmd->Ejecutar();

			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idgrupo=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_grupos($idgrupo,$nombregrupo,$literaltipo);
				$baseurlimg="../images/signos";
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaNodo(0);
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE grupos SET nombregrupo=@nombregrupo,comentarios=@comentarios WHERE idgrupo=@idgrupo";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaGrupos($cmd,$idgrupo,"idgrupo",$literaltipo);// Eliminación en cascada
			break;
		default:
			break;
	}
	return($resul);
}
/*________________________________________________________________________________________________________
	Crea un arbol XML para el nuevo nodo insertado 
________________________________________________________________________________________________________*/
function SubarbolXML_grupos($idgrupo,$nombregrupo,$literaltipo){

	$cadenaXML='<GRUPOS';
	// Atributos
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$literaltipo."'" .')"';
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$nombregrupo.'"';
	$cadenaXML.=' nodoid='.$literaltipo.'-'.$idgrupo;
	$cadenaXML.='>';
	$cadenaXML.='</GRUPOS>';
	return($cadenaXML);
}
/*________________________________________________________________________________________________________
	Elimina en cascada grupos
		Parametros: 
		- cmd: Un comando ya operativo (con conexión abierta)  
		- idgrupo: El identificador del grupo
		- literaltipo: El literal del grupo
		- literaltipo: El literal del grupo
		- swid: Indica 0= El identificador es tipo alfanumérico	1= EI identificador es tipo numérico ( valor por defecto)
________________________________________________________________________________________________________*/
function	EliminaGrupos($cmd,$identificador,$nombreid,$literaltipo,$swid=1){
	if (empty($identificador)) return(true);

	global $LITAMBITO_GRUPOSAULAS ;
	global $LITAMBITO_GRUPOSIMAGENESMONOLITICAS ;
	global $LITAMBITO_GRUPOSIMAGENESBASICAS ;
	global $LITAMBITO_GRUPOSIMAGENESINCREMENTALES ;
	global $LITAMBITO_GRUPOSPROCEDIMIENTOS ;
	global $LITAMBITO_GRUPOSTAREAS ;
	global $LITAMBITO_GRUPOSTRABAJOS ;
	global $LITAMBITO_GRUPOSCOMPONENTESHARD  ;
	global $LITAMBITO_GRUPOSCOMPONENTESSOFT ;
	global $LITAMBITO_GRUPOSPERFILESHARD ;
	global $LITAMBITO_GRUPOSPERFILESSOFT ;
	global $LITAMBITO_GRUPOSSOFTINCREMENTAL ;
	global $LITAMBITO_GRUPOSMENUS ;
	global $LITAMBITO_GRUPOSREPOSITORIOS ;
	global $LITAMBITO_GRUPOSRESERVAS ;
	global $LITAMBITO_GRUPOSENTIDADES ;

	if($swid==0)
		$cmd->texto="SELECT  idgrupo  FROM grupos WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='SELECT  idgrupo  FROM grupos WHERE '.$nombreid.'='.$identificador;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if ($rs->numeroderegistros==0) return(true);
	$rs->Primero(); 
	while (!$rs->EOF){
		$resul=EliminaGrupos($cmd,$rs->campos["idgrupo"],"grupoid",$literaltipo);
		if ($resul){
			switch($literaltipo){
				case $LITAMBITO_GRUPOSAULAS :
					$resul=EliminaAulas($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSPROCEDIMIENTOS :
					$resul=EliminaProcedimientos($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSTAREAS :
					$resul=EliminaTareas($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSTRABAJOS :
					$resul=EliminaTrabajos($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSIMAGENESMONOLITICAS :
					$resul=EliminaImagenes($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSIMAGENESBASICAS :
					$resul=EliminaImagenes($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSIMAGENESINCREMENTALES :
					$resul=EliminaImagenes($cmd,$rs->campos["idgrupo"],"grupoid");
					break;					
				case $LITAMBITO_GRUPOSCOMPONENTESHARD  :
					$resul=EliminaHardwares($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSCOMPONENTESSOFT :
					$resul=EliminaSoftwares($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSPERFILESHARD :
					$resul=EliminaPerfileshard($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSPERFILESSOFT :
					$resul=EliminaPerfilessoft($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSSOFTINCREMENTAL :
					$resul=EliminaSoftincremental($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSREPOSITORIOS :
					$resul=Eliminarepositorios($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSMENUS :
					$resul=EliminaMenus($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSRESERVAS :
					$resul=EliminaReservas($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				case $LITAMBITO_GRUPOSENTIDADES :
					$resul=EliminaEntidad($cmd,$rs->campos["idgrupo"],"grupoid");
					break;
				default:
						$resul=false;
			}
		}
		if (!$resul){
			$rs->Cerrar();
			return(false);
		}
		$rs->Siguiente();
	}
	if($swid==0)
		$cmd->texto="DELETE FROM grupos WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='DELETE FROM grupos  WHERE '.$nombreid.'='.$identificador;
	$resul=$cmd->Ejecutar();
	return($resul);
}
?>

