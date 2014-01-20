<?php

class algorithm{

	private $banks = array();
	const MAX_GENERATIONS = 50000;
	const FITNESS_THRESHOLD = 0;

	public function __construct($banks = NULL)
	{
		try{
			if(is_null($banks)){
				throw new Exception('Error, no se ha especificado # de bancos');
			}
			$this->init($banks);
		}catch(Exception $e){
			die($e->getMessage());
		}		
	}
	
	private function init($banks)
	{
		for($x=0; $x<$banks; $x++){
			$this->banks[$x] = new Population();
		}
	}
	
	public function getBank($bank = NULL){
		try{
			if(is_null($bank)){
				throw new Exception('Error, falta especificar banco');
			}
			return $this->banks[$bank];
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	public function getBestCandidates($bank = NULL){
		try{
			if(is_null($bank)){
				throw new Exception('Error, falta especificar banco');
			}
			return $this->banks[$bank]->getBestCitizens();
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	public function setObjective($bank = NULL, $objective = NULL){
		try{
			if(is_null($bank) || is_null($objective)){
				throw new Exception('Error, falta especificar banco u objetivo');
			}
			$this->banks[$bank]->setObjective($objective);
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	public function startUp($bank = NULL)
	{
		try{
			if(is_null($bank)){
				throw new Exception('Error, falta especificar banco');
			}
			$this->banks[$bank]->startUp();
			$this->banks[$bank]->calculateFitness();
			$this->banks[$bank]->assignBestCitizens();
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	public function isFinished($bank = NULL, $fitness_threshold = 0){
		try{
			if(is_null($bank)){
				throw new Exception('Error, falta especificar banco');
			}
			$bests = $this->getBestCandidates($bank);
			$can0 = $bests[0]->getFitness();
			return ($can0 == $fitness_threshold);
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	function microtime_float()
  {
    list($useg, $seg) = explode(" ", microtime());
    return ((float)$useg + (float)$seg);
  }
	
	public function start($bank = NULL){
		try{
			if(is_null($bank)){
				throw new Exception('Error, falta especificar banco');
			}
			//BUCLE HASTA ENCONTRAR EL MEJOR CANDIDATO
			$end = FALSE;
			$generation = 0;
			$stime = $this->microtime_float();
			while($end == FALSE && $generation < self::MAX_GENERATIONS){
				//CREAMOS CANDIDATOS A PARTIR DE LOS DOS MEJORES CANDIDATOS DE LA GENERACIÓN ANTERIOR
				$this->banks[$bank]->reproduce();

				//MUTAMOS LOS CANDIDATOS DE LA GENERACIÓN ACTUAL
				$this->banks[$bank]->mutate();

				//EVALUAMOS LOS CANDIDATOS Y ESTABLECEMOS LOS DOS MEJORES DE ESTA GENERACIÓN
				$this->banks[$bank]->calculateFitness();
				$this->banks[$bank]->assignBestCitizens();

				//PRINTAMOS POR PANTALLA LOS DOS MEJORES CANDIDATOS DE ESTA GENERACIÓN
				$bests = $this->banks[$bank]->getBestCitizens();
				echo "GENERATION: $generation" . PHP_EOL."<br>";
				$i = 0;
				foreach($bests as $b){
				  ++$i;
				  echo "Best Candidate $i: " . $b->getData() . " - " . $b->getFitness() . PHP_EOL."<br>";
				}
				echo PHP_EOL . PHP_EOL;

				$generation++;
				$end = $this->isFinished(0, self::FITNESS_THRESHOLD);
			}
			$ftime = $this->microtime_float();
			echo "Time evolving: " . ($ftime - $stime) . 'sec' . PHP_EOL . PHP_EOL;
			return $this->banks[$bank]->getBestCitizens();
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
}

class Population{
	
	const MAX_POPULATION = 10000;
	private $population = array();
	private $bestCitizens = array();
	private $objective = '';
	
	public function __construct(){
		$this->init();
	}
	
	private function init(){
		for($x=0; $x<self::MAX_POPULATION; $x++){
			$this->population[$x] = new Citizen();
		}
		
		$this->bestCitizens[0] = new Citizen();
    $this->bestCitizens[0]->setFitness(1000);
	}
	
	public function calculateFitness(){
		for($x=0; $x<self::MAX_POPULATION; $x++){
			$this->population[$x]->calculateFitness($this->objective);
		}
	} 
	
	public function setObjective($objective = NULL){
		try{
			if(is_null($objective)){
				throw new Exception('Error, no se ha especificado objetivo');
			}
			$this->objective = $objective;
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	public function startUp(){
		for($x=0; $x<self::MAX_POPULATION; $x++){
			$this->population[$x]->setData($this->randomData(strlen($this->objective)));
		}
	}
	
	private function randomData($length){
		$temp = '';
		for($x=0;$x<$length;$x++){
			$temp .= chr(mt_rand(32, 126));
		}
		return $temp;
	}
	
	private function cmp($a, $b){
		$a = $a->getFitness();
		$b = $b->getFitness();
		
	    if ($a == $b) {
	        return 0;
	    }
	    return ($a < $b) ? -1 : 1;
	}
	
	public function assignBestCitizens(){
		usort($this->population, array($this,"cmp"));
		
	  $this->bestCitizens[0]->setData($this->population[0]->getData());
  	$this->bestCitizens[0]->setFitness($this->population[0]->getFitness());
	}
	
	public function getBestCitizens(){
		return $this->bestCitizens;
	}
	
	public function reproduce(){
	  $best = $this->population[0]->getData();
		for($x=1; $x<self::MAX_POPULATION; $x++){
		  $temp = $this->population[$x]->getData();
      for($y=0;$y<strlen($this->objective);$y++){
        if(mt_rand(0,1)){
          $temp[$y] = $best[$y];
        }
      }
      
  		$this->population[$x]->setData($temp);
		}
	}
	
	public function mutate(){
		for($x=0; $x<self::MAX_POPULATION; $x++){
			$data = $this->population[$x]->getData();
			for($y=0;$y<strlen($this->objective);$y++){
			  if(mt_rand()%100<4){
			    $data[$y] = chr(mt_rand(32, 126));
			  }
			}
			
			$this->population[$x]->setData($data);
		}
	}
}

class Citizen{
	
	private $data;
	private $fitness;
	
	public function __construct()
	{
		$this->data = '';
		$this->fitness = -1;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function setData($newData = NULL)
	{
		try{
			if(!is_null($newData)){
				$this->data = $newData;
			}
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	public function getFitness()
	{
		return $this->fitness;
	}
	
	public function setFitness($newFitness = NULL)
	{
		try{
			if(!is_null($newFitness)){
				$this->fitness = $newFitness;
			}
		}catch(Exception $e){
			die($e->getMessage());
		}
			
	}
	
	public function calculateFitness($objective = NULL){
		try{
			if(is_null($objective)){
				throw new Exception('Error, no se ha especificado objetivo');
			}
			
			$fitness = 0;
			for($x=0;$x<strlen($objective);$x++){
				$fitness += abs(ord($objective[$x]) - ord($this->data[$x]));
			}
			
			$this->setFitness($fitness);
		}catch(Exception $e){
			die($e->getMessage());
		}
		
	}
	
}
?>