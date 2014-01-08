<?
class torrent {
	public $data,$array,$inn;

	private $has_mbstring = false, $has_mb_shadow = false;

	private function is_assoc($array) {
		return (bool)count(array_filter(array_keys($array), 'is_string'));
	}
	private function my_strlen($data) {
		if ($this->has_mbstring && ($this->has_mb_shadow & 2) ) {
			return mb_strlen($data,'latin1');
		} else {
			return strlen($data);
		}
	}
	private function my_substr($str , $start , $length = false ) {
		if ($this->has_mbstring && ($this->has_mb_shadow & 2) ) {
			return mb_substr ($str , $start , $length===false?$this->my_strlen($str):$length, 'latin1');
		} else {
			if($length===false){
				return substr($str,$start);
			} else {
				return substr($str,$start,$length);
			}
		}
	}

	function load($bin) {
		$raw=$bin;
		$this->data=$bin;
		$this->array=$this->parse($raw);
	}

	function is_dir() {
		return isset($this->array['info']['files']);
	}
	function is_single_file() {
		return !isset($this->array['info']['files']);
	}

	function __construct($raw=false) {
		$this->has_mbstring = extension_loaded('mbstring') ||@dl(PHP_SHLIB_PREFIX.'mbstring.'.PHP_SHLIB_SUFFIX);
		$this->has_mb_shadow = (int) ini_get('mbstring.func_overload');
		if($raw!==false && $this->my_strlen($raw)>0) {
			$this->load($raw);
		}
	}
	function parse(&$raw) {
		if($this->my_strlen($raw)==0) return false;
		$this->inn++;
		$return=false;
		$char=$raw[0];
		switch($char) {
			case is_numeric($char): {
					$p=explode(':',$raw,2);
					if(count($p)!=2) break;
					$p[1]=$this->my_substr($p[1], 0, (int)$p[0]);
					$raw=$this->my_substr($raw, $this->my_strlen($p[0])+1+(int)$p[0]);
					$return=$p[1];
				} break;
			case 'd': {
					$out=array();
					$raw=$this->my_substr($raw,1);
					while($this->my_strlen($raw)>0 && $raw[0]!='e') {
						$key=$this->parse($raw);
						$val=$this->parse($raw);
						$out[$key]=$val;
					}
					if($raw[0]=='e') $raw=$this->my_substr($raw,1);
					$return=$out;
				} break;
			case 'i': {
					$p=explode("e",$this->my_substr($raw, 1),2);
					$raw=$p[1];
					$return=(int)$p[0];
				} break;
			case 'l': {
					$out=array();
					$raw=$this->my_substr($raw,1);
					while($this->my_strlen($raw)>0 && $raw[0]!='e') {
						$out[]=$this->parse($raw);
					}
					if($raw[0]=='e') $raw=$this->my_substr($raw,1);
					$return=$out;
				} break;
			case 'e': {
					return 'UnEXPECTED END';
				} break;
		}
		return $return;
	}
	function save($file=false) {
		$raw=$this->code($this->array);
		if($file!==false && is_string($file)) {
			$h=fopen($file, 'w');
			fwrite($h, $raw);
			fclose($h);
			return true;
		} else {
			return $raw;
		}
	}
	function code($data) {
		$return='';
		switch ($data) {
			case is_integer($data): // i
					$return='i'.$data.'e';
				break;
			case is_array($data) && $this->is_assoc($data):
					$return='d';
					ksort($data);
					foreach ($data as $key => $value) {
						$return.=$this->code($key).$this->code($value);
					}
					$return.='e';
				break;
			case is_array($data) && !$this->is_assoc($data):
					$return='l';
					foreach ($data as $value) {
						$return.=$this->code($value);
					}
					$return.='e';
				break;
			case is_string($data):
					$return=$this->my_strlen($data).':'.$data;
				break;
		}
		return $return;
	}
}
?>