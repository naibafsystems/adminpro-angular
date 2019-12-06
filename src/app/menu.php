<?php
if (session_id() == "") {
    session_start();
}
if (isset($_REQUEST['periodoSeleccion']) && $_REQUEST['periodoSeleccion'] != 0) {
    $_SESSION['anoproc'] = substr($_REQUEST['periodoSeleccion'], 0, 4);
    $_SESSION['trimestre'] = substr($_REQUEST['periodoSeleccion'], 4, 2);
}
include '../../persistencia/conecta.php';
include '../../persistencia/funciones.php';
/*
 * Modulo anexo para controlar el tiempo de sesion en el historico de novedades
 * Realizado por: laescobarj
 * Descripcion: Funcion que controla el tiempo de permanencia en el m�dulo de novedades
 * 				acorde al tiempo de sesion de usuarios. 
 */
echo cierrasesion();

/* * ******************************************************************************
 * FIN DE VALIDACION CONTROL DE SESSIONES
 * ****************************************************************************** */


$array01 = array(6310, 6320, 6331, 6332, 6333, 6339, 6390, 7010, 7020, 7111, 7112, 7121, 7122, 7123, 7129, 7130,
    7310, 7320, 7411, 7412, 7413, 7414, 7421, 7422, 7495, 7493, 7499, 7494, 9213, 9211, 9212, 9220, 9214, 9219, 9301, 9302, 9303, 9309, 9241, 9242, 9249);
$array02 = array(5521, 5522, 5523, 5524, 5529, 5530);
$array03 = array(6411, 6412);
$array04 = array(6421, 6422, 6425, 6426, 6423, 6424);
$array05 = array(7210, 7220, 7230, 7240, 7250, 7290);
$array06 = array(7430);
$array07 = array(7491);
$array08 = array(7492);
$array09 = array(8050);
$array10 = array(8511, 8512, 8513, 8514, 8515, 8519);

$ident_usu = $_SESSION['idusu'];
$cod_regi = $_SESSION['region'];
$anoproc = $_SESSION['anoproc'];

$trimproc = $_SESSION['trimestre'];

if ($anoproc >= 2017) {
    $mes = mesLetras($trimproc);
    $trim = $mes . "-" . $anoproc;
} else {
    $trim = $anoproc . "-" . $trimproc;
}

if (isset($_GET['atras'])) {
    unset($_GET['atras']);
}

$periodo = $_SESSION['anoproc'];
$descestado = "";
if (isset($_GET['idemp'])) {
    $idemp = $_GET['idemp'];
} else {
    $idemp = $_SESSION['numero'];
}

//Consulta que hace referencia al a�o y periodo a partir se aplica un cambio en los m�dulos del sistema
$sql = 'SELECT id_refcambios, anio, periodo FROM  mtsb_param_refcambios WHERE id_refcambios=1';

$resultado = mysql_query($sql, $con);
$listaResultados = mysql_fetch_array($resultado);
$result = mysql_query($sql, $con);

$anioSinCPC = $listaResultados['anio'];    //a�o en desde que se est� trabajando con otros ingresos fijos

$conctl = "	SELECT *, 
					CASE estado 
						WHEN 0 THEN 'Pendiente' 
						WHEN 1 THEN 'Distribuido'
						WHEN 2 THEN 'En Digitaci&oacute;n' 
						WHEN 3 THEN 'Formulario Completo'
				 	END AS letras 
				FROM mtsb_admin_control WHERE idnoremp=" . $idemp . " AND periodo = " . $periodo . " AND trimestre = " . $trimproc;
//echo $conctl."<br>";
//vista rapida de consulta
mostrar_consulta("menu # 1", $conctl);

$resctl = mysql_query($conctl, $con);
if (mysql_num_rows($resctl) > 0) {
    $linctl = mysql_fetch_array($resctl);
} else {
    $linctl['letras'] = "";
    $linctl['m1'] = "";
    $linctl['m2'] = "";
    $linctl['m3'] = "";
    $linctl['ciiu3'] = "";
    $linctl['estado'] = "";
}

/**
 * arreglo del estado de los periodos
 * @author Jonathan Esquivel <jresquivelf@dane.gov.co>
 * @since 23/03/2012
 */
/* 	
  $consregctl = "SELECT * FROM mtsb_param_regcontrol ";
  if($cod_regi != 99) {
  $consregctl .= "WHERE estado = 0 ";
  }
  $consregctl .= "ORDER BY periodoproc, trimproc DESC";
  $resregctl = mysql_query($consregctl, $con);
  $linregctl = mysql_fetch_array($resregctl);
  $periproc = $linregctl['periodoproc'];
  $trimestre = $linregctl['trimproc'];
 */
$mdili = "";
if ($idemp != 0) {
    $conempre = mysql_query("SELECT idnoremp, idact,ciiu4, idnomcom, operativo, redisenio, redisenioEMSB 
									FROM mtsb_admin_directorio 
									WHERE idnoremp = $idemp", $con);
   
    //vista rapida de consulta
    mostrar_consulta("menu # 2", $conctl);

    $linempre = mysql_fetch_array($conempre);

    $actividad = substr($linempre['ciiu4'], 0, -2);
    $ciiu = $linempre['ciiu4']; 
    $_SESSION['ciiu4'] =$ciiu;  
   
    if ($actividad == 56) { //Si es restaurantes
        $mdili = "../../archivos/PES-MTS-MDI-02.pdf";
        $fborra = "../../archivos/FORMULARIO_I2.pdf";
    } elseif ($actividad == 61) { //Si es telecomunicaciones
        $mdili = "../../archivos/PES-MTS-MDI-03.pdf";
        $fborra = "../../archivos/FORMULARIO_J3.pdf";
    } elseif ($actividad == 85) { //Si es educaci�n
        $mdili = "../../archivos/PES-MTS-MDI-04.pdf";
        $fborra = "../../archivos/FORMULARIO_P.pdf";
    } else {
        $mdili = "../../archivos/PES-MTS-MDI-01.pdf";
        $fborra = "../../archivos/FORMULARIO_GENERAL.pdf";
    }

    /* if (in_array($linempre['idact'], $array01)) {
      $mdili = "../../archivos/PES-MTS-MDI-01.pdf";
      $fborra = "../../archivos/FORMULARIO_GENERAL.pdf";
      }
      if (in_array($linempre['idact'], $array02)) {
      $mdili = "../../archivos/PES-MTS-MDI-02.pdf";
      $fborra = "../../archivos/FORMULARIO_H1.pdf";
      }
      if (in_array($linempre['idact'], $array03)) {
      $mdili = "../../archivos/PES-MTS-MDI-03.pdf";
      $fborra = "../../archivos/FORMULARIO_I2.pdf";
      }
      if (in_array($linempre['idact'], $array04)) {
      $mdili = "../../archivos/PES-MTS-MDI-04.pdf";
      $fborra = "../../archivos/FORMULARIO_I3.pdf";
      }
      if (in_array($linempre['idact'], $array05)) {
      $mdili = "../../archivos/PES-MTS-MDI-05.pdf";
      $fborra = "../../archivos/FORMULARIO_K2.pdf";
      }
      if (in_array($linempre['idact'], $array06)) {
      $mdili = "../../archivos/PES-MTS-MDI-06.pdf";
      $fborra = "../../archivos/FORMULARIO_K4.pdf";
      }
      if (in_array($linempre['idact'], $array07)) {
      $mdili = "../../archivos/PES-MTS-MDI-07.pdf";
      $fborra = "../../archivos/FORMULARIO_7491.pdf";
      }
      if (in_array($linempre['idact'], $array08)) {
      $mdili = "../../archivos/PES-MTS-MDI-08.pdf";
      $fborra = "../../archivos/FORMULARIO_7492.pdf";
      }
      if (in_array($linempre['idact'], $array09)) {
      $mdili = "../../archivos/PES-MTS-MDI-09.pdf";
      $fborra = "../../archivos/FORMULARIO_M.pdf";
      }
      if (in_array($linempre['idact'], $array10)) {
      $mdili = "../../archivos/PES-MTS-MDI-10.pdf";
      $fborra = "../../archivos/FORMULARIO_N.pdf";
      } */
}

$tipo_usuario = substr($_SESSION['tipou'], 0, 2);
/* AGREGAR NIVEL DE ACCESO */
$_SESSION['acceso'] = substr($_SESSION['tipou'], 2, 1);

switch ($tipo_usuario) {
    case "CO":
        $descmenu = "Men&uacute; Coordinador";
        if (isset($linempre['idnomcom'])) {
            $descmenu = "Men&uacute; Coordinador [" . trim($linempre['idnomcom']) . "]";
        }
        if (!isset($_GET['nombre'])) {
            $menu = "	<a class='menuc' href='../formulario/caratula.php' target='contenido' title='Actualizar informaci&oacute;n Car&aacute;tula &uacute;nica'>Directorio</a>
							<a class='menuc' href='../administracion/usuarios.php' target='contenido' title='Administraci&oacute;n y Consulta de Usuarios'>Usuarios</a>
							<a class='menuc' href='../formulario/buscar.php' target='contenido' title='Ingreso a revisi&oacute;n de formularios'>Formularios</a>
							<a class='menuc' href='../administracion/operativo.php' target='contenido' title='Consultar Estado del Operativo'>Operativo</a>
                                                        <a class='menuc' href='../administracion/operativoredi.php' target='contenido' title='Consultar estado del operativo del redise�o'>Operativo R</a>
						";
            if ($cod_regi == 99) {
                $menu .= "<a class='menuc' href='../formulario/crearFuentes.php' target='contenido' title='Administraci&oacute;n de m&oacute;dulos'>Administrador</a>";
                $menu .= "<a class='menuc' href='../formulario/capitulos.php' target='contenido' title='Descarga de archivos a formato Excel'>Descargar cap&iacute;tulos</a>";
                $menu .= "<a class='menuc' href='../formulario/capitulosHistorico.php' target='contenido' title='Descarga de archivos Hist�ricos a formato Excel'>Hist�rico cap&iacute;tulos</a>";
                $tipoUsu = substr($_SESSION['tipou'], 2, 1);
                if ($tipoUsu == "B") {
                    $menu .= "<a class='menuc' href='../administracion/cierremts.php' target='contenido' title='Ejecuta proceso de cierre trimestre actual'>Cierre Mes</a>";
                    $menu .= "<a class='menuc' href='../administracion/cierremtsbTotal.php' target='contenido' title='Ejecuta proceso de cierre trimestre actual'>Cierre Total</a>";
                } else {
                    $menu .= "<a class='menuc' href='../administracion/cierremts.php' target='contenido' title='Ejecuta proceso de cierre trimestre actual'>Cierre Mes</a>";
                    $menu .= "<a class='menuc' href='../administracion/cierremtsRedisenio.php' target='contenido' title='Ejecuta proceso de cierre redse&ntilde;o'>Cierre Redi</a>";
                }
            /* jacoronadoc creacion de menu para los nuevos modulos */ 
            $menu .= "<a class='menuc' href='resul_generales.php' target='contenido' title='Resultados Generales'>Resultados Generales</a>";
            $menu .= "<a class='menuc' href='grafica_resul_generales.php' target='contenido' title='Resultados Generales'>Gr&aacute;fica Resultados Generales</a>";
            $menu .= "<a class='menuc' href='grafica_seccion.php' target='contenido' title='Grafica Seccion'>Gr&aacute;fica Secci&oacute;n</a>";
			$menu .= "<a class='menuc' href='cargue_parametricas.php' target='contenido' title='Cargue parametricas'>Cargue Parametricas</a>";
			$menu .= "<a class='menuc' href='nacionalvsbogota.php' target='contenido' title='Nacional VS Bogot&aacute;'>Nacional VS Bogot&aacute;</a>";
			$menu .= "<a class='menuc' href='modulo_resumen.php' target='contenido' title='Módulo resumen'>Módulo Resumen</a>";

            }
            $menu .= "<a class='menuc' href='indcal.php?periodo=" . $anoproc . "' target='contenido' title='Generar Indicador de Calidad'>Ind. Calidad</a>";
        } else {
            $qstrempre = "?idemp=" . $_GET['idemp'] . "&nombre=" . $_GET['nombre'];

            $menu = "<a class='menuc' href='../formulario/caratula.php" . $qstrempre . "' target='contenido' title='Actualizar Informaci&oacute;n Car&aacute;tula &Uacute;nica'>Modulo I</a>";
			
            if ($_GET['idact'] > 8010 AND $_GET['idact'] < 8091) {
                $qstremp1 = "&nomcap=Personal ocupado Promedio en el mes (educaci�n)&tabla=mtsb_form_persedu' ";
                $menu .= "<a class='menuc' href='../../interfaz/formulario/perseduca.php" . $qstrempre . $qstremp1 . "target='contenido' title='Actualizar Informaci&oacute;n de Personal y Costos y Gastos del Personal'>Modulo II</a>";
            } else {
                $qstremp1 = "&nomcap=Personal ocupado Promedio en el mes&tabla=mtsb_form_personal' ";
                $menu .= "<a class='menuc' href='../../interfaz/formulario/personal.php" . $qstrempre . $qstremp1 . "target='contenido' title='Actualizar Informaci&oacute;n de Personal y Costos y Gastos del Personal'>Modulo II</a>";
            }
            $qstremp2 = "&nomcap=Ingresos Causados en el mes&tabla=mtsb_form_ingresos' ";
            $menu .= "<a class='menuc' href='../../interfaz/formulario/ingresos.php" . $qstrempre . $qstremp2 . "target='contenido' title='Actualizar Informaci&oacute;n Ingresos Causados en el Trimestre'>Modulo III</a>";

            if ($periodo >= $anioSinCPC) {
                echo "";
            } else {
                //L�neas para el m�dulo IV
                $qstremp3 = "&nomcap=NORMAS INTERNACIONALES DE INFORMACI�N FINANCIERA&tabla=mtsb_form_ingresos'";
                $menu .= "<a class='menuc' href='../../interfaz/formulario/niif.php" . $qstrempre . $qstremp3 . "' target='contenido' title='Implementaci&oacute;n de normas internacionales de informaci&oacute;n financiera NIIF'>Modulo IV</a>";
            }

            $menu .= "<a class='menuc' href='menu.php?atras=si' title='Regresar al menu principal'>Volver Men&uacute; ppal</a>";
            $menu .= "<a class='menuc' href='../../logica/diagfor.php" . $qstrempre . "' target='contenido' title='Ficha de An&aacute;lisis de la Empresa'>Ficha An&aacute;lisis</a>";
            $menu .= "<a class='menuc' href='logcambios.php" . $qstrempre . "' target='contenido' title='Log de cambios de la Empresa'>Log cambios</a>";
            if ($linctl['estado'] == 5) {
                $menu .= "<a class='menuc' id='indica2' href='registro.php" . $qstrempre . "' target ='contenido' title='Generar Paz y Salvo Empresa'>Paz y Salvo</a>";
            }
        }
        break;

    case "CR":
        $descmenu = "Men&uacute; Cr&iacute;tico - Analista";
        if (isset($linempre['idnomcom'])) {
            $descmenu = "Men&uacute; Cr&iacute;tico - Analista [" . trim($linempre['idnomcom']) . "]";
        }
        if (!isset($_GET['nombre'])) {
            if ($cod_regi == 99) {
                $menu = "<a class='menuc' href='../../interfaz/formulario/caratula.php' target='contenido' title='Descarga de archivos a formato Excel'>Directorio</a>";
            } else {
                $menu = "<a class='menuc' href='../../interfaz/formulario/listacara.php' target='contenido' title='Actualizar informaci&oacute;n Car&aacute;tula &uacute;nica'>Directorio</a>";
            }
            if ($tipo_usuario == "CR" AND $cod_regi == 99) {
                $menu .= "<a class='menuc' href='../../interfaz/formulario/buscar.php' target='contenido' title='Ingreso a revisi&oacute;n de formularios'>Formularios</a>";
            } else {
                $menu .= "<a class='menuc' href='../../interfaz/formulario/listacap.php' target='contenido' title='Ingreso a revisi&oacute;n de formularios'>Formularios</a>";
            }
            $menu .= "<a class='menuc' href='../../interfaz/administracion/operativo.php' target='contenido' title='Consultar Estado del Operativo'>Operativo</a>";
            $menu .= "<a class='menuc' href='../../interfaz/administracion/operativoredi.php' target='contenido' title='Consultar Estado del Operativo de redise�o'>Operativo R</a>";
            if ($cod_regi == 99) {
                $menu .= "<a class='menuc' href='../formulario/capitulos.php' target='contenido' title='Descarga de archivos a formato Excel'>Descargar cap&iacute;tulos</a>";
            }
            if ($cod_regi == 99) { //Solo para criticos de DANE Central
                //$menu .= "<a class='menuc' href='../formulario/logcambios.php' target='contenido' title='Log de Cambios'>Log de Cambios</a>";
                 $menu .= "<a class='menuc' href='logcambios.php" . $qstrempre . "' target='contenido' title='Log de cambios de la Empresa'>Log cambios</a>";
            }
        } else {
            $qstrestab = "?idemp=" . $_GET['idemp'] . "&nombre=" . $_GET['nombre'];
            $menu = "<a class='menuc' href='../formulario/caratula.php" . $qstrestab . "' target='contenido' title='Descarga de archivos a formato Excel'>Modulo I</a>";
            if ($_GET['idact'] > 8010 AND $_GET['idact'] < 8091) {
                $qstresp1 = "&nomcap=Personal ocupado Promedio en el mes (educaci�n)&tabla=mtsb_form_persedu'";
                $menu .= "<a class='menuc' href='../../interfaz/formulario/perseduca.php" . $qstrestab . $qstresp1 . " target='contenido' title='Actualizar Informaci&oacute;n de Personal y Costos y Gastos del Personal'>Modulo II</a>";
            } else {
                $qstresp1 = "&nomcap=Personal ocupado Promedio en el mes&tabla=mtsb_form_personal'";
                $menu .= "<a class='menuc' href='../formulario/personal.php" . $qstrestab . $qstresp1 . " target='contenido' title='Actualizar Informaci&oacute;n de Personal y Costos y Gastos del Personal'>Modulo II</a>";
            }
            $qstresp2 = "&nomcap=Ingresos Causados en el mes&tabla=mtsb_form_ingresos'";
            $menu .= "<a class='menuc' href='../../interfaz/formulario/ingresos.php" . $qstrestab . $qstresp2 . " target='contenido' title='Atualizar Informaci&oacute;n Ingresos Causados en el Trimestre'>Modulo III</a>";
            if ($periodo >= $anioSinCPC) {
                echo "";
            } else {
                //L�neas para el m�dulo IV
                $qstresp3 = "&nomcap=NORMAS INTERNACIONALES DE INFORMACI�N FINANCIERA&tabla=mtsb_form_ingresos'";
                $menu .= "<a class='menuc' id='ingreso' href='../formulario/niif.php" . $qstrestab . $qstresp3 . "' target='contenido' title='Implementaci&oacute;n de normas internacionales de informaci&oacute;n financiera NIIF'>Modulo IV</a>";
            }
            $menu .= "<a class='menuc' href='menu.php?atras=si' title='Regresar al menu principal'>Volver Men&uacute; ppal.</a>";
            $menu .= "<a class='menuc' href='../../logica/diagfor.php" . $qstrestab . "' target='contenido' title='Ficha de An&aacute;lisis de la Empresa'>Ficha An&aacute;lisis</a>";
            if ($linctl['estado'] == 5) {
                $menu .= "<a class='menuc' id='indica2' href='registro.php" . $qstrestab . "' target ='contenido' title='Generar Paz y Salvo Empresa'>Paz y Salvo</a>";
            }
        }
        break;
    case "FU":
        $descmenu = trim($linempre['idnomcom']);
        $descestado .= " [" . $linctl['letras'] . "]";
        $qstrestab = "?idemp=" . $linempre['idnoremp'] . "&nombre=" . $linempre['idnomcom'];
        if ($linctl['m2'] != 2) {
            $nombreclase = "menuc";
        } elseif ($linctl['m2'] == 2) {
            $nombreclase = "menucd";
        }
        if ($linctl['ciiu3'] > 8010 AND $linctl['ciiu3'] < 8091) {
            $programa = "../../interfaz/formulario/perseduca.php";
            $qstrestab .= "&nomcap=Personal ocupado Promedio en el mes (educaci�n)&tabla=mtsb_form_persedu";
        } else {
            $programa = "../../interfaz/formulario/personal.php";
            $qstrestab .= "&nomcap=Personal ocupado Promedio en el mes&tabla=mtsb_form_personal";
        }
        if ($linctl['m1'] != 2) {
            $menu = "<a class='menuc' id='opm1' href='../../interfaz/formulario/caratula.php' target='contenido' title='Actualizar Informaci&oacute;n Car&aacute;tula &Uacute;nica'>Modulo I</a>";
        } elseif ($linctl['m1'] == 2) {
            $menu = "<a class='menucd' id='opm1' href='../../interfaz/formulario/caratula.php' target='contenido' title='Actualizar Informaci&oacute;n Car&aacute;tula &Uacute;nica'>Modulo I</a>";
        }
        $menu .= "<a class='" . $nombreclase . "' id='perso' href='" . $programa . $qstrestab . "' target='contenido' title='Actualizar Informaci&oacute;n de Personal Ocupado y Costos y Gastos del Personal'>Modulo II</a>";
        $qstrestab = "?idemp=" . $linempre['idnoremp'] . "&nombre=" . $linempre['idnomcom'] . "&nomcap=Ingresos causados en el mes (MILES DE PESOS)&tabla=mtsb_form_ingresos";
        if ($linctl['m3'] != 2) {
            $menu .= "<a class='menuc' id='ingreso' href='../formulario/ingresos.php" . $qstrestab . "' target='contenido' title='Acualizar Informaci&oacute;n de Ingresos Causados en el mes'>Modulo III</a>";
        } elseif ($linctl['m3'] == 2) {
            $menu .= "<a class='menucd' id='ingreso' href='../formulario/ingresos.php" . $qstrestab . "' target='contenido' title='Acualizar Informaci&oacute;n de Ingresos Causados en el mes'>Modulo III</a>";
        }
        if ($periodo >= $anioSinCPC) {
            echo "";
        } else {
            //L�neas para el m�dulo IV
            if ($linctl['m4'] != 2) {
                $menu .= "<a class='menuc' id='ingreso' href='../formulario/niif.php" . $qstrestab . "' target='contenido' title='Implementaci&oacute;n de normas internacionales de informaci&oacute;n financiera NIIF'>Modulo IV</a>";
            } elseif ($linctl['m4'] == 2) {
                $menu .= "<a class='menucd' id='ingreso' href='../formulario/niif.php" . $qstrestab . "' target='contenido' title='Implementaci&oacute;n de normas internacionales de informaci&oacute;n financiera NIIF'>Modulo IV</a>";
            }
        }
        $paramenv = "?idemp=" . $linempre['idnoremp'] . "&periodo=" . $anoproc . "&trimes=" . $trimproc;
        if ($periodo >= $anioSinCPC) {
            if ($linctl['m1'] + $linctl['m2'] + $linctl['m3'] == 6) {
                $menu .= "<a class='menuf' id='indica' href='../formulario/fichaempre.php" . $paramenv . "' target ='contenido' title='Resumen principales indicadores econ&oacute;micos de la Empresa'>Indicadores</a>";
            }
        } else {
            if ($linctl['m1'] + $linctl['m2'] + $linctl['m3'] + $linctl['m4'] == 8) {
                $menu .= "<a class='menuf' id='indica' href='../formulario/fichaempre.php" . $paramenv . "' target ='contenido' title='Resumen principales indicadores econ&oacute;micos de la Empresa'>Indicadores</a>";
            }
        }
        if ($linctl['estado'] == 5) {
            $menu .= "<a class='menuc' id='indica2' href='registro.php" . $qstrestab . "' target ='contenido' title='Generar Paz y Salvo Empresa'>Paz y Salvo</a>";
        }
        break;
    case "TE":
        $descmenu = "Men&uacute; Tem�tico";
        if (isset($linempre['idnomcom'])) {
            $descmenu = "Men&uacute; Coordinador [" . trim($linempre['idnomcom']) . "]";
        }
        if (!isset($_GET['nombre'])) {
            $menu = "<a class='menuc' href='../formulario/caratula.php' target='contenido' title='Car&aacute;tula &Uacute;nica'>Directorio</a>
					<a class='menuc' href='../formulario/buscar.php' target='contenido' title='Revisi&oacute;n de Formularios'>Formularios</a>";
            /*
              if ($cod_regi == 99) {
              $menu .= "<a class='menuc' href='../formulario/capitulos.php' target='contenido' onMouseOver='muestraOpcion(\"desc\"); return false;'>Descargar cap&iacute;tulos</a>";
              }

              $menu .= "<a class='menuc' href='../administracion/indcal.php' target='contenido'>Ind. Calidad</a>";
             */

            $menu .= "<a class='menuc' href='indcal.php?periodo=" . $anoproc . "' target='contenido' title='Generar Indicador de Calidad'>Ind. Calidad</a>";
        } else {
            $qstrempre = "?idemp=" . $_GET['idemp'] . "&nombre=" . $_GET['nombre'];
            $menu = "<a class='menuc' href='../formulario/caratula.php" . $qstrempre . "' target='contenido' title='Car&aacute;tula &Uacute;nica'>Modulo I</a>";
            if ($_GET['idact'] > 8010 AND $_GET['idact'] < 8091) {
                $qstremp1 = "&nomcap=Personal ocupado Promedio en el mes (educaci�n)&tabla=mtsb_form_persedu' ";
                $menu .= "<a class='menuc' href='../../interfaz/formulario/perseduca.php" . $qstrempre . $qstremp1 . "target='contenido' title='Personal Ocupado y Costos y Gastos de Personal'>Modulo II</a>";
            } else {
                $qstremp1 = "&nomcap=Personal ocupado Promedio en el mes&tabla=mtsb_form_personal' ";
                $menu .= "<a class='menuc' href='../../interfaz/formulario/personal.php" . $qstrempre . $qstremp1 . "target='contenido' title='Personal Ocupado y Costos y Gastos de Personal'>Modulo II</a>";
            }
            $qstremp2 = "&nomcap=Ingresos Causados en el mes&tabla=mtsb_form_ingresos' ";
            $menu .= "<a class='menuc' href='../../interfaz/formulario/ingresos.php" . $qstrempre . $qstremp2 . "target='contenido' title='Ingresos Causados en el Trimestre'>Modulo III</a>";
            $menu .= "<a class='menuc' href='menu.php?atras=si' onMouseOver='muestraOpcion(\"mpp\"); return false;'>Volver Men&uacute; ppal</a>";
            $menu .= "<a class='menuc' href='../../logica/diagfor.php" . $qstrempre . "' target='contenido'>Ficha An&aacute;lisis</a>";
        }
        break;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>DANE - Encuesta Mensual de Servicios - Formulario Electr&oacute;nico</title>
        <script type="text/javascript" src="../../js/mopcion.js"></script>
        <link rel = stylesheet href = "../../css/formelec.css" type="text/css">
    </head>
    <body style="background-color: #F8F9FE">
    <center><img style="float:center" src="../../imagenes/logo_ems.png"></center>
    <div id="nav1" style="width: 100%; height: 15%; background-color: #F8F9FE;">
        <div id="nav2" style="float: left; display: inline; width: 70%; height: 100%; background-color: #F8F9FE;">
            <!--img style="float:left" src="../../imagenes/logo_ems.png"><br><br><br-->
            <span style="font-family: arial; font-size: 14px; font-weight: bold; color: #000; padding-left: 10px">Formulario Electr&oacute;nico - <?php echo $trim ?></span> -
            <span style="font-family: arial; font-size: 14px; font-weight: bold; color: #990000; padding-left: 10px"><?php echo $descmenu ?></span><span class="estado"><?php echo $descestado ?></span><br>
            <?php
           
            if ($tipo_usuario == 'CO' OR $tipo_usuario == 'CR' OR $tipo_usuario == 'TE') {
                if (substr($_SESSION['tipou'], 2, 1) == "T" OR substr($_SESSION['tipou'], 2, 1) == "") {
                    $operativo = "MTS";
                } elseif (substr($_SESSION['tipou'], 2, 1) == "B") {
                    $operativo = "MTSB";
                } elseif (substr($_SESSION['tipou'], 2, 1) == "R") {
                    $operativo = "EMSR";
                }
                $sql_periodos = "SELECT * FROM mtsb_param_regcontrol WHERE operativo = '" . $operativo . "'";
                if ($cod_regi != 99) {
                    $sql_periodos .= " AND estado IN (0,1)";
                }
                $sql_periodos .= " ORDER BY secperiodo DESC";
            } elseif ($tipo_usuario == 'FU') {
                if($linempre['operativo']=='MTS'){
                    $sql_periodos = "SELECT distinct(a.periodoproc), a.trimproc, a.estado FROM mtsb_param_regcontrol a WHERE a.estado IN (1)  AND operativo = 'MTS'  ORDER BY secperiodo DESC";
                }else{
                    $sql_periodos = "SELECT distinct(a.periodoproc), a.trimproc, a.estado FROM mtsb_param_regcontrol a WHERE a.estado IN (1)  AND operativo = 'MTSB'  ORDER BY secperiodo DESC";
                }    
                //$sql_periodos1 = "SELECT a.* FROM mtsb_param_regcontrol a WHERE a.estado IN (0,1)  AND periodoproc >= 2017 AND operativo = '".$linempre['operativo']."'  ORDER BY CONCAT(a.periodoproc, a.trimproc) DESC";
            } else {
//						$sql_periodos = "SELECT * FROM mtsb_param_regcontrol WHERE CONCAT(periodoproc, trimproc) NOT IN (SELECT CONCAT(periodo, trimestre) FROM    
//							mtsb_admin_control WHERE idnoremp = " . $idemp . " AND estado > 3 AND novedad = 99) ORDER BY periodoproc, trimproc DESC";
                $sql_periodos = "SELECT a.* FROM mtsb_param_regcontrol a WHERE a.estado = 1  AND operativo = " . $linempre['operativo'] .
                        " ORDER BY secperiodo DESC";
            }
           //echo $sql_periodos;
            $periodo_array = mysql_query($sql_periodos, $con);

            //echo $sql_periodos;     
            if ($tipo_usuario == 'CO' OR $tipo_usuario == 'CR' OR $tipo_usuario == 'TE') {
                echo "<div style='float: left; position: relative; left: 5px; padding-top: 1px; z-index: 2;' id='per'>";
                echo "<form name='formPeriodo' method='POST' action='menu.php'>";
                echo "<select name='periodoSeleccion' id='periodoSeleccion' onchange='submit()'>";
                while ($arreglo_periodo = mysql_fetch_array($periodo_array)) {
                    if ($arreglo_periodo['periodoproc'] >= 2017) {
                        //Invoco la funci�n donde convierto los meses en letras
                        $mes = mesLetras($arreglo_periodo['trimproc']);
                        $trimperi = $arreglo_periodo['periodoproc'] . $arreglo_periodo['trimproc'];
                        if ($arreglo_periodo['periodoproc'] == $_SESSION['anoproc'] AND $arreglo_periodo['trimproc'] == $_SESSION['trimestre']) {
                            echo "<option value='" . $trimperi . "' selected>" . $arreglo_periodo['periodoproc'] . " - " . $mes . "</option>";
                        } else {
                            echo "<option value='" . $trimperi . "'>" . $arreglo_periodo['periodoproc'] . " - " . $mes . "</option>";
                        }
                    } else {
                        if ($tipo_usuario != 'FU') {
                            $trimperi = $arreglo_periodo['periodoproc'] . $arreglo_periodo['trimproc'];
                            if ($arreglo_periodo['periodoproc'] == $_SESSION['anoproc'] AND $arreglo_periodo['trimproc'] == $_SESSION['trimestre']) {
                                echo "<option value='" . $trimperi . "' selected>" . $arreglo_periodo['periodoproc'] . " - " . $arreglo_periodo['trimproc'] . "</option>";
                            } else {
                                echo "<option value='" . $trimperi . "'>" . $arreglo_periodo['periodoproc'] . " - " . $arreglo_periodo['trimproc'] . "</option>";
                            }
                        }
                    }
                }
                echo "</select>";
                echo "</form>";
                echo "</div>";
            } elseif ($tipo_usuario == 'FU') {
                
                if(($linempre['redisenio']=='S' || $linempre['redisenio']=='C') || $linempre['redisenioEMSB']=='S' || $linempre['operativo']=='MTSB'){
                //if(($linempre['redisenio']=='S' || $linempre['redisenio']=='C') || $linempre['redisenioEMSB']=='S'){
                echo "<div style='float: left; position: relative; left: 5px; padding-top: 1px; z-index: 2;' id='per'>";
                echo "<form name='formPeriodo' method='POST' action='menu.php'>";
                echo "<select name='periodoSeleccion' id='periodoSeleccion' onchange='submit()'>";
                while ($arreglo_periodo = mysql_fetch_array($periodo_array)) {
                    //Invoco la funci�n donde convierto los meses en letras
                    $mes = mesLetras($arreglo_periodo['trimproc']);
                    $trimperi = $arreglo_periodo['periodoproc'] . $arreglo_periodo['trimproc'];
                        if ($arreglo_periodo['periodoproc'] == $_SESSION['anoproc'] AND $arreglo_periodo['trimproc'] == $_SESSION['trimestre']) {
                            echo "<option value='" . $trimperi . "' selected>". $arreglo_periodo['periodoproc'] . " - " . $mes . "</option>";
                        } else {
                            echo "<option value='" . $trimperi . "'>" . $arreglo_periodo['periodoproc'] . " - " . $mes . "</option>";
                            echo "";
                        }
                     
                }
                echo "</select>";
                echo "</form>";
                echo "</div>";
                }else{
                            echo "";
                        }
            }
            ?>
            <div style='position: relative; left: 2%; padding-top: 1px;' id='adm'>
                <?php
                echo $menu;
                ?>
            </div>
            <div style='position: relative; left: 15%; font-family: arial; font-size: 10px' id='txtdescrip'></div>
        </div>
        <div id="nav3" style="float: right; display: inline; width: 25%; text-align: right; font-family: verdana; color: #333366;">
            <span style="font-family: arial; font-size: 12px; font-weight: bold">
                <?php
                if ($tipo_usuario == "FU")
                    echo trim($linempre['idnomcom']);
                else
                    echo trim(utf8_encode($_SESSION['nombreu']));
                ?>
            </span><br>
            <a class="liscara" href="../administracion/cambioclav.php" target="contenido">Cambiar Clave</a>&nbsp;&nbsp;

            <a class="liscara" href="../../index.php?salir" target="_top"> Finalizar Sesi&oacute;n</a><br /><br /> 
            <!-- 	 			<a class="formato" href="?salir" target="_blank" title="Salir del programa">Finalizar Sesi&oacute;n</a>	 			 -->
            <!--			<a class="formato" href="archivos/SI-EAS2010-MUS-01.pdf" target="_top">ManualUsuario</a><br> -->
            <a class="formato" href="<?php echo $mdili ?>" target="_blank" title="Descargar Manual de Diligenciamiento">MDiligenciamiento</a>
            <a class="formato" href="<?php echo $fborra ?>" target="_blank" title="Descargar formato en blanco para borrador [PDF]">FormularioBorrador</a>
            <?php
            if (isset($_GET['nombre'])) {
                $qstrestab = "?idemp=" . $_GET['idemp'] . "&nombre='" . $_GET['nombre'] . "' ";
                echo "<a class='formato' href='../formulario/frmimpre.php" . $qstrestab . "' target='_blank'>Formulario Diligenciado</a>&nbsp";
            }
            if ($tipo_usuario == "FU") {
                echo "<a class='formato' href='../formulario/frmimpre.php' target='_blank'>Formulario Diligenciado</a>&nbsp";
            }
            ?>
        </div>

        <?php
        if ($tipo_usuario == 'FU') {

            if ($periodo >= $anioSinCPC) {
                if ($linctl['m1'] + $linctl['m2'] + $linctl['m3'] == 6) {
                    echo "<script type='text/javascript'>
							parent.frames['cabeza'].document.getElementById('indica').style.visibility = 'visible'
							</script>";
                }
            } else {
                if ($linctl['m1'] + $linctl['m2'] + $linctl['m3'] + $linctl['m4'] == 8) {
                    echo "<script type='text/javascript'>
							parent.frames['cabeza'].document.getElementById('indica').style.visibility = 'visible'
							</script>";
                }
            }
            if ($linctl['estado'] == 5) {
                echo "<script type='text/javascript'>
						parent.frames['cabeza'].document.getElementById('indica2').style.visibility = 'visible'
						</script>";
            }
            $ciiu3 = $_SESSION['ciiu3'];
        }
        ?>
    </div>
</body>
</html>