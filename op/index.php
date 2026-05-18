<?php 

class DataGeo {
private $db;
    function __construct() {

        $dsn = "mysql:host=192.168.90.71;dbname=simnangkis_sleman";
        $user = "kimis";
        $passwd = "mi5kin987";
        $this->db = new PDO($dsn, $user, $passwd);
    }

     public function response()
    {
     
      $SQL="SELECT id_kel,kelurahan,id_kec,id_kab,id_prop,poly from master_kel order by id_prop,id_kab,id_kec,id_kel asc";

      $resultArray =  $this->db->query($SQL);

     $data=$resultArray->fetchALL(PDO::FETCH_ASSOC);

     foreach ($data as $key) {
     	// print_r($key['poly']);
     	$x=$this->reverseGeo($key['poly']);
     	$id_kec=$key['id_kec'];
     	$kel=$key['kelurahan'];
     	// print_r($kel);exit();
     	// print_r('<br>'.'<br>'.'<br>'.$x);exit;
     	$update="UPDATE master_kel set poly1='$x' where kelurahan='$kel'";
     	// print_r($update);exit;
     	$stmt=$this->db->prepare($update);
     	$stmt->execute();
     }
     //print_r(json_encode($data));
     $this->db=null;
     exit;
     
     
      header('Content-Type: application/json');
      echo json_encode($response);

    }

    public function reverseGeo($par){
    	$res1=array();
    	$res2=array();
    	$array=explode( ',',$par);
    	// print_r($array);echo'<br>';
    	
    	foreach ($array as $key) {
    		$key=str_replace('  ', ' ', $key);
    		$arr=explode(' ',$key);
    		// print_r($arr);echo '<br>';
    		if($arr[0]=='')
    		$arr2=$arr[2].' '.$arr[1];    		
    		else
			$arr2=$arr[1].' '.$arr[0];    			
    		// print_r($arr2.'<br>');
    		array_push($res1, $arr2);
    	}
    	// exit;
    	return implode(", ", $res1);
    }

    public function getGeoKec($par){
    	$id_kec=$par->id_kec;

    	$SQL="SELECT id_kec,kecamatan,id_kab,id_prop, replace(replace(AsText(geom),'POLYGON((',''),'))','') poly from master_kec where id_kec='$id_kec'";
		// print_r($SQL);exit();
      	$resultArray =  $this->db->query($SQL);
    	$data=$resultArray->fetch(PDO::FETCH_ASSOC);
    	// print_r($data['kecamatan']);exit();
    	$kecArr = array();
    	$kec = new stdClass();
    	$prop = new stdClass();
    	$geom = new stdClass();

    	$prop->nama= $data['kecamatan'];
    	$prop->jenis= "Kecamatan";
    	$geom->type="Polygon";
    	$x=$this->getArray($data['poly']);
    	// $y=array();
    	// array_push($y, $x);
    	$geom->coordinates=array();
    	array_push($geom->coordinates, $x);

    	$kec->type="Feature";
    	$kec->properties = $prop;
    	$kec->geometry = $geom;

     	array_push($kecArr,$kec);

    	$return = new stdClass();
    	$return->type="FeatureCollection";
    	$return->features=$kecArr;
    	// print_r($return);
    	$this->db=null;
    	header('Content-Type: application/json');
      	echo json_encode($return);
      	// echo json_encode($kecArr);
    }



    public function getGeoKel($par){
    	$id_kec=$par->id_kec;
    	$id_kel=$par->id_kel;
    	$SQL="SELECT id_kec,id_kel,kelurahan,id_kab,id_prop, replace(replace(AsText(geom),'POLYGON((',''),'))','') poly from master_kel where id_kec='$id_kec' and id_kel='$id_kel' ";
		// print_r($SQL);exit();
      	$resultArray =  $this->db->query($SQL);
    	$data=$resultArray->fetch(PDO::FETCH_ASSOC);
    	// print_r($data['kecamatan']);exit();
    	$kecArr = array();
    	$kec = new stdClass();
    	$prop = new stdClass();
    	$geom = new stdClass();

    	$prop->nama= $data['kelurahan'];
    	$prop->jenis= "Kelurahan";
      $prop->kecamatan= $this->getKecamatan($data['id_kec']);
    	$geom->type="Polygon";
    	$x=$this->getArray($data['poly']);
    	// $y=array();
    	// array_push($y, $x);
    	
    	$geom->coordinates=array();
    	array_push($geom->coordinates, $x);

    	$kec->type="Feature";
    	$kec->properties = $prop;
    	$kec->geometry = $geom;

     	array_push($kecArr,$kec);

    	$return = new stdClass();
    	$return->type="FeatureCollection";
    	$return->features=$kecArr;
    	// print_r($return);
    	$this->db=null;
    	header('Content-Type: application/json');
      	echo json_encode($return);
      	// echo json_encode($kec);
    }

    public function getKabKec(){


    	$SQL="SELECT id_kec,kecamatan,id_kab,id_prop, replace(replace(AsText(geom),'POLYGON((',''),'))','') poly from master_kec";
		// print_r($SQL);exit();
      	$resultArray =  $this->db->query($SQL);
    	$data=$resultArray->fetchALL(PDO::FETCH_ASSOC);
    	// print_r($data['kecamatan']);exit();
    	$kecArr= array();
    	foreach ($data as $key ) {
    			$kec = new stdClass();
		    	$prop = new stdClass();
		    	$geom = new stdClass();

		    	$prop->nama= $key['kecamatan'];
		    	$prop->jenis= "Kecamatan";
		    	$geom->type="Polygon";
		    	$x=$this->getArray($key['poly']);
		    	$y=array();
		    	array_push($y, $x);
		    	$geom->coordinates=array();
		    	array_push($geom->coordinates, $y);

		    	$kec->type="Feature";
		    	$kec->properties = $prop;
		    	$kec->geometry = $geom;

		    	array_push($kecArr,$kec);
    	}
    	

     	

    	$return = new stdClass();
    	$return->type="FeatureCollection";
    	$return->features=$kecArr;
    	// print_r($return);
    	$this->db=null;
    	header('Content-Type: application/json');
      	echo json_encode($return);
    }

    public function getKabKel(){

    	$SQL="SELECT id_kec,id_kel,kelurahan,id_kab,id_prop, replace(replace(AsText(geom),'POLYGON((',''),'))','') poly from master_kel order by id_kec, id_kel";
		// print_r($SQL);exit();
      	$resultArray =  $this->db->query($SQL);
    	$data=$resultArray->fetchALL(PDO::FETCH_ASSOC);
    	// print_r($data['kecamatan']);exit();
    	$kecArr= array();
    	foreach ($data as $key) {
    			$kec = new stdClass();
		    	$prop = new stdClass();
		    	$geom = new stdClass();

		    	$prop->nama= $key['kelurahan'];
		    	$prop->jenis= "Kelurahan";
          $prop->Kecamatan=$this->getKecamatan($key['id_kec']);
		    	$geom->type="Polygon";
		    	$x=$this->getArray($key['poly']);
          // print_r($key['poly']);exit;
		    	$geom->coordinates=array();
		    	array_push($geom->coordinates, $x);

		    	$kec->type="Feature";
		    	$kec->properties = $prop;
		    	$kec->geometry = $geom;

		    	array_push($kecArr,$kec);
    	}

    	

     	

    	$return = new stdClass();
    	$return->type="FeatureCollection";
    	$return->features=$kecArr;
    	// print_r($return);
    	$this->db=null;
    	header('Content-Type: application/json');
      	echo json_encode($return);
    }

    public function getArray($par){
    	$res1=array();
    	$array=explode( ',',$par);
    	foreach ($array as $key) {
    		$arr=explode(' ',$key);
    		$arr1=array((float)$arr[0],(float)$arr[1]);

    		array_push($res1, $arr1);
    		
    	}
    	return $res1;
    }

    public function getGeoJson($par){
    	$type=$par->type;
    	if ($type=='kec'){
    		$this->getKabKec();
    	}else{
    		$this->getKabKel();
    	}
    }

    public function getKecamatan($id_kec){

      $sql="select kecamatan from master_kec where id_kec='$id_kec'";
      // print_r($sql);
      $stmt = $this->db->prepare($sql); 
      $stmt->execute(); 
      $row = $stmt->fetch();

      // return $row;
      return $row['kecamatan'];
    }

}

$theParams = explode("/", $_SERVER["PATH_INFO"]);
$path_count = count($theParams);

// print_r($path_count);exit;
$par1 = $theParams[$path_count-2];
$par2 = $theParams[$path_count-1];



$x=json_decode($par2);	



// // print_r($x->id_kec);exit;
$obj = new DataGeo();
// // $obj->response();
// // $obj->getGeoKel('01','2001');
// if isset($x)

$obj->$par1($x);
// $obj->$par1();