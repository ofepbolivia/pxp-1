<?php
session_start();

/*
 * Autor: RCM
 * Fecha: 11/09/2013
 * Propósito: Clase contenedora de plantillas html para generación de reportes: Header, Footer, Tabla normal, tablas especiales, etc.
 */
class PlantillasHtml{
	
	private $strNombrePlantilla='';
	private $intIndicePlantilla=-1;
	private $strPlantillaEval;
	
	private $arrPlantillas=array();
	private $arrPlantillasKeys=array();
	private $arrValoresDefecto=array();
	
	private $strMarca='**';
	private $objParam;
	
	//////////////
	//Constructor
	//////////////
	
	function __construct(CTParametro $pObjParam){
		$this->objParam=$pObjParam;
		$this->definirValoresDefecto();
		$this->definirPlantillasDefecto();
	}
	
	///////////
	//Métodos
	//////////
	protected function definirPlantillasDefecto(){
		/*
		 * Para definir las plantillas, se debe definir la plantilla en el array plantilla, y obligatotiamente definir sus Clasves (keys) en el arrary
		 * arrPlantillasKeys, tanto el nombre del array como el índice deben coincidir
		 */
		 
		//Claves (Keys) genéricas
		$this->arrPlantillasKeys['generic'][0] = array('main_title1','main_title2','main_pagina_actual','main_pagina_total','main_user','main_sistema','main_barcode',
		'main_ruta_logo','main_date');
		
		/////////
		//Header
		/////////
		$this->arrPlantillasKeys['header'][0]=array('header_key_right1','header_value_right1','header_key_right2','header_value_right2',
														'header_key_right3','header_value_right3');	
		$this->arrPlantillas['header'][0]='
			<table border="1" cellspacing="0" cellpadding="1">
				<tr>
					<td width="23%" rowspan="4"><img src="../../lib/lib_reporte/logo.png" border="0" width=156 height=117 /></td>
					<td align="center" width="54%" rowspan="2">**main_title1**</td>
					<td width="23%">**header_key_right1**: **header_value_right1**</td>
				</tr>
				<tr>
					<td width="23%">**header_key_right2**: **header_value_right2**</td>
				</tr>
				<tr>
					<td align="center" width="54%" rowspan="2">**main_title2**</td>
					<td width="23%">**header_key_right3**: **header_value_right3**</td>
				</tr>
				<tr>
					<td width="23%">Página **main_pagina_actual** de **main_pagina_total**</td>
				</tr>
			</table>';
		
		/////////												
		//Footer
		/////////
		$this->arrPlantillasKeys['footer'][0]=array();
		$this->arrPlantillas['footer'][0]='
			<table border="1" cellspacing="0" cellpadding="1">
				<tr>
					<td width="30%">
						Usuario: **main_user**<br>
						Fecha: **main_date** 
					</td>
					<td align="center" width="40%">
						**main_sistema**
					</td>
					<td align="right" width="30%">
						Control: **main_barcode**
					</td>
				</tr>
			</table>';
		
		$this->arrPlantillasKeys['footer'][1]=array('footer_col1_fil1','footer_col2_fil1','footer_col3_fil1','footer_col4_fil1',
													'footer_col1_fil2','footer_col2_fil2','footer_col3_fil2','footer_col4_fil2',
													'footer_col1_fil3','footer_col2_fil3','footer_col3_fil3','footer_col4_fil3',
													'footer_col1_fil4','footer_col2_fil4','footer_col3_fil4','footer_col4_fil4');								
		
		$this->arrPlantillas['footer'][1]='
			<table border="1" cellspacing="0" cellpadding="1">
				<tr>
					<td width="25%">**footer_col1_fil1**</td>
					<td width="25%">**footer_col2_fil1**</td>
					<td width="25%">**footer_col3_fil1**</td>
					<td width="25%">**footer_col4_fil1**</td>
				</tr>
				<tr>
					<td width="25%" height="50">**footer_col1_fil2**</td>
					<td width="25%">**footer_col2_fil2**</td>
					<td width="25%">**footer_col3_fil2**</td>
					<td width="25%">**footer_col4_fil2**</td>
				</tr>
				<tr>
					<td width="25%">**footer_col1_fil3**</td>
					<td width="25%">**footer_col2_fil3**</td>
					<td width="25%">**footer_col3_fil3**</td>
					<td width="25%">**footer_col4_fil3**</td>
				</tr>
				<tr>
					<td width="25%">**footer_col1_fil14**</td>
					<td width="25%">**footer_col2_fil4**</td>
					<td width="25%">**footer_col3_fil4**</td>
					<td width="25%">**footer_col4_fil4**</td>
				</tr>
			</table>';
			
		/////////	
		//Tablas
		////////
		$this->arrPlantillasKeys['table'][0]=array();
		$this->arrPlantillas['table'][0]=array();		
		
	}

	protected function definirValoresDefecto(){
		$this->arrValoresDefecto=array('url_archivo'=>'../../../reportes_generados/','nombre_archivo'=>'reporte_pdf','creator'=>'pXP','author'=>'Kplian','title1'=>'REGISTRO',
									'subject'=>'File generated by pXP framework','keywords'=>'TCDF, PDF, pXP, HTML, kerp, kplian','title2'=>'Reporte PXP',
									'main_user'=>$_SESSION["_NOM_USUARIO"],'main_sistema'=>'kERP');
	}

	//Reemplaza las marcas por los valores dinámicos
	protected function evaluarPlantilla(){
		//Verifica que se haya seleccionado la plantilla
		$this->verificarSiPlantillaDef();
		
		//Copia la plantilla en la variable de salida
		$this->strPlantillaEval=$this->arrPlantillas[$this->strNombrePlantilla][$this->intIndicePlantilla];
		
		//Evalua las variables principales
		foreach ($this->arrPlantillasKeys['generic'][0] as $key => $val) {
			$aux = $this->strMarca.$val.$this->strMarca;
			$pos = strpos($this->strPlantillaEval, $aux);
			if($pos){
				$this->strPlantillaEval = str_replace($aux, $this->getValorPlantilla($val), $this->strPlantillaEval);
			}	
		}
		
		//Evalúa las variables de la plantilla
		foreach ($this->arrPlantillasKeys[$this->strNombrePlantilla][$this->intIndicePlantilla] as $key => $val) {
			$aux = $this->strMarca.$val.$this->strMarca;
			$pos = strpos($this->strPlantillaEval, $aux);
			if($pos){
				$this->strPlantillaEval = str_replace($aux,$this->getValorPlantilla($val), $this->strPlantillaEval);
			}	
		}
	}

	protected function verificarSiPlantillaDef(){
		if($this->strNombrePlantilla=='' OR $this->intIndicePlantilla==-1){
			throw new Exception(__METHOD__.': Plantilla no definida (ejecute propiedad: setSeleccionarPlantilla');
		}
		return true;
	}
	
	protected function getValorPlantilla($pKey){
		if($pKey=='main_title1'){
			$resp=$this->getValorDef('title1');
		} else if($pKey=='main_title2'){
			$resp=$this->getValorDef('title2');
		} else if($pKey=='main_ruta_logo'){
			$resp=$this->getValorDef('ruta_logo');
		} else if($pKey=='main_user'){
			$resp=$this->getValorDef('main_user');
		} else if($pKey=='main_sistema'){
			$resp=$this->getValorDef('main_sistema');
		} else if($pKey=='main_date'){
			$resp=date('d-m-Y H:i:s');
		} else if($this->objParam->getParametro($pKey)!=''){
			$resp=$this->objParam->getParametro($pKey);
		} else {
			$resp=$this->strMarca.$pKey.$this->strMarca;
		}
		return $resp;
	}
	
	//Devuelve el valor de la Llave, buscando primero en el objeto parametro y si no hay en los valores por defecto
	protected function getValorDef($pKey){
		$aux=$this->objParam->getParametro($pKey)!=''?$this->objParam->getParametro($pKey):$this->arrValoresDefecto[$pKey];
		return $aux;
	}
	
	
	//////////////
	//Propiedades
	/////////////
	
	//Devuelve la plantilla evaluada y lista
	public function getPlantilla(){
		//Evalúa la plantilla
		$this->evaluarPlantilla();
		//Respuesta
		return $this->strPlantillaEval; 
	}
	
	public function setSeleccionarPlantilla($pPlantilla,$pIndice=0){
		$this->strNombrePlantilla=$pPlantilla;
		$this->intIndicePlantilla=$pIndice;
	}
}

?>