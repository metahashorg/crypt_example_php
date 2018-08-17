<?php
define('ROOT_DIR', __DIR__);
define('DATA_DIR', ROOT_DIR.'/data');

if(file_exists(DATA_DIR) == false)
{
	exit('`data` directory not found in '.ROOT_DIR);
}

if(floatval(phpversion()) < 7.1)
{
	exit('requirements PHP 7.1+, current '.phpversion());
}

if(extension_loaded('curl') == false)
{
	exit('php-curl extension not loaded');
}

if(extension_loaded('mhcrypto') == false)
{
	throw new Exception('mhcrypto extension not loaded');
}

class Ecdsa
{
	public function __construct()
	{
		
	}

	public function getKey()
	{
		$result = [
			'private' => null,
			'public' => null,
			'address' => null
		];

		mhcrypto_generate_wallet($result['private'], $result['public'], $result['address']);

		foreach($result as &$val)
		{
			$val = $this->to_base16($val);
		}

		return $result;
	}

	public function privateToPublic($private_key)
	{
		$public_key = null;
		mhcrypto_generate_public($private_key, $public_key);
		$result = '0x'.$public_key;

		return $result;
	}

	public function sign($data, $private_key)
	{
		$sign = null;
		mhcrypto_sign_text($sign, $private_key, $data);

		return '0x'.bin2hex($sign);
	}

	public function verify($sign, $data, $public_key)
	{
		return mhcrypto_check_sign_text($this->hex2bin($sign), $public_key, $data);
	}

	public function getAdress($key)
	{
		$address = null;
		mhcrypto_generate_address($key, $address);

		return '0x'.$address;
	}

	public function checkAdress($address)
	{
		if(!empty($address))
		{
			return mhcrypto_check_address($address);
		}

		return false;
	}

	public function to_base16($string)
	{
		return (substr($string, 0, 2) === '0x')?$string:'0x'.$string;
	}

	public function parse_base16($string)
	{
		return (substr($string, 0, 2) === '0x')?substr($string, 2):$string;
	}
}

class IntHelper
{
	public function __construct(){}

	public static function Int8($i, $hex = false)
	{
		$res = is_int($i)?self::Pack('c', $i, $hex):self::UnPack('c', $i, $hex)[1];
		return $res;
	}

	public static function UInt8($i, $hex = false)
	{
		return is_int($i)?self::Pack('C', $i, $hex):self::UnPack('C', $i, $hex)[1];
	}

	public static function Int16($i, $hex = false)
	{
		return is_int($i)?self::Pack('s', $i, $hex):self::UnPack('s', $i, $hex)[1];
	}

	public static function UInt16($i, $hex = false, $endianness = false)
	{
		$f = is_int($i)?'Pack':'UnPack';

		if($endianness === true) // big-endian
		{  
			$i = self::$f('n', $i, $hex);
		}
		elseif($endianness === false) // little-endian 
		{  
			$i = self::$f('v', $i, $hex);
		}
		elseif($endianness === null) // machine byte order
		{  
			$i = self::$f('S', $i, $hex);
		}

		return is_array($i)?$i[1]:$i;
	}

	public static function Int32($i, $hex = false)
	{
		return is_int($i)?self::Pack('l', $i, $hex):self::UnPack('l', $i, $hex)[1];
	}

	public static function UInt32($i, $hex = false, $endianness = false)
	{
		$f = is_int($i)?'Pack':'UnPack';

		if ($endianness === true) // big-endian
		{
			$i = self::$f('N', $i, $hex);
		}
		else if ($endianness === false) // little-endian
		{  
			$i = self::$f('V', $i, $hex);
		}
		else if ($endianness === null) // machine byte order
		{
			$i = self::$f('L', $i, $hex);
		}

		return is_array($i)?$i[1]:$i;
	}

	public static function Int64($i, $hex = false)
	{
		return is_int($i)?self::Pack('q', $i, $hex):self::UnPack('q', $i, $hex)[1];
	}

	public static function UInt64($i, $hex = false, $endianness = false)
	{
		$f = is_int($i)?'Pack':'UnPack';

		if ($endianness === true) // big-endian
		{
			$i = self::$f('J', $i, $hex);
		}
		else if ($endianness === false) // little-endian
		{  
			$i = self::$f('P', $i, $hex);
		}
		else if ($endianness === null) // machine byte order
		{
			$i = self::$f('Q', $i, $hex);
		}

		return is_array($i) ? $i[1] : $i;
	}

	public static function VarUInt($i, $hex = false)
	{
		if(is_int($i))
		{
			if($i < 250)
			{
				return self::UInt8($i, $hex);
			}
			elseif($i < 65536)
			{
				return  self::UInt8(250, $hex).self::UInt16($i, $hex);
			}
			elseif($i < 4294967296)
			{
				return self::UInt8(251, $hex).self::UInt32($i, $hex);
			}
			else
			{
				return self::UInt8(252, $hex).self::UInt64($i, $hex);
			}
		}
		else
		{
			$l = strlen($i);
			if($l == 2)
			{
				return self::UInt8($i, $hex);
			}
			elseif($l == 4)
			{
				return  self::UInt16($i, $hex);
			}
			elseif($l == 6)
			{
				return  self::UInt16(substr($i, 2), $hex);
			}
			elseif($l == 8)
			{
				return self::UInt32($i, $hex);
			}
			elseif($l == 10)
			{
				return  self::UInt32(substr($i, 2), $hex);
			}
			elseif($l == 18)
			{
				return  self::UInt64(substr($i, 2), $hex);
			}
			else
			{
				return self::UInt64($i, $hex);
			}
		}
	}

	private static function Pack($mode, $i, $hex = false)
	{
		return $hex?bin2hex(pack($mode, $i)):pack($mode, $i);
	}

	private static function UnPack($mode, $i, $hex = false)
	{
		return $hex?unpack($mode, hex2bin($i)):unpack($mode, $i);
	}
}

function is_base64_encoded($data)
{
	$data = str_replace("\r\n", '', $data);
	$chars = array('+', '=', '/', '-');
	$n = 0;
	foreach($chars as $val)
	{
		if(strstr($data, $val))
		{
			$n++;
		}
	}

	return ($n > 0 && base64_encode(base64_decode($data, true)) === $data)?true:false;
}

function write_file($path, $data, $mode = 'wb')
{
	if(!$fp = @fopen($path, $mode))
	{
		return FALSE;
	}

	flock($fp, LOCK_EX);

	for($result = $written = 0, $length = strlen($data); $written < $length; $written += $result)
	{
		if (($result = fwrite($fp, substr($data, $written))) === FALSE)
		{
			break;
		}
	}

	flock($fp, LOCK_UN);
	fclose($fp);
	return is_int($result);
}

function is_really_writable($file)
{
	if(DIRECTORY_SEPARATOR === '/')
	{
		return is_writable($file);
	}

	if(is_dir($file))
	{
		$file = rtrim($file, '/').'/'.md5(mt_rand());
		if (($fp = @fopen($file, 'ab')) === FALSE)
		{
			return FALSE;
		}
		fclose($fp);
		@chmod($file, 0777);
		@unlink($file);
		return TRUE;
	}
	elseif(!is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE)
	{
		return FALSE;
	}
	fclose($fp);

	return TRUE;
}

function is_cli()
{
	return (PHP_SAPI === 'cli' OR defined('STDIN'));
}

function debug($data)
{
	echo '<pre>'; print_r($data); echo '</pre>';
}

class Crypto
{
	private $ecdsa = null;
	public $debug = false;
	public $net = null;

	private $curl = null;
	private const PROXY = ['url' => 'proxy.net-%s.metahash.org', 'port' => 9999];
	private const TORRENT = ['url' => 'tor.net-%s.metahash.org', 'port' => 5795];
	private $hosts = [];
	
	public function __construct($ecdsa)
	{
		$this->ecdsa = $ecdsa;
		$this->curl = curl_init();
	}

	public function generate()
	{
		$data = $this->ecdsa->getKey();
		$data['address'] = $this->ecdsa->getAdress($data['public']);

		if($this->saveAddress($data))
		{
			return $data;
		}

		return false;
	}

	public function checkAdress($address)
	{
		return $this->ecdsa->checkAdress($address);
	}

	private function saveAddress($data = [])
	{
		if($fp = fopen(DATA_DIR.'/'.$data['address'].'.mh', 'w'))
		{
			if(fputcsv($fp, $data, "\t"))
			{
				fclose($fp);
				return true;
			}
			fclose($fp);
		}

		return false;
	}

	public function readAddress($address)
	{
		if(file_exists(DATA_DIR.'/'.$address.'.mh'))
		{
			if(($fp = fopen(DATA_DIR.'/'.$address.'.mh', "r")) !== FALSE)
			{
				if(($data = fgetcsv($fp, 1000, "\t")) !== FALSE)
				{
					return [
						'private' => $data[0],
						'public' => $data[1], 
						'address' => $data[2]
					];
				}
				
				fclose($fp);
			}
		}

		return false;
	}

	public function listAddress()
	{
		$result = [];
		if($res = scandir(DATA_DIR))
		{
			foreach($res as $val)
			{
				if(strstr($val, '.mh'))
				{
					$result[] = str_replace('.mh', '', $val);
				}
			}
		}

		return $result;
	}

	public function fetchBalance($address)
	{
		return $this->queryTorrent('fetch-balance', ['address' => $address]);
	}

	public function fetchHistory($address)
	{
		return $this->queryTorrent('fetch-history', ['address' => $address]);
	}

	public function getTx($hash)
	{
		return $this->queryTorrent('get-tx', ['hash' => $hash]);
	}

	public function sendTx($to, $value, $fee = '', $nonce = 1, $data = '', $key = '', $sign = '')
	{
		$data = [
			'to' => $to,
			'value' => strval($value),
			'fee' => strval($fee),
			'nonce' => strval($nonce),
			'data' => $data,
			'pubkey' => $key,
			'sign' => $sign
		];

		return $this->queryProxy('mhc_send', $data);
	}

	public function getNonce($address)
	{
		$res = $this->fetchBalance($address);
		return (isset($res['result']['count_spent']))?intval($res['result']['count_spent']) + 1:1;
	}

	public function makeSign($address, $value, $nonce, $fee = 0, $data = '')
	{
		$a = (substr($address, 0, 2) === '0x')?substr($address, 2):$address; // адрес
		$b = IntHelper::VarUInt(intval($value), true); // сумма
		$c = IntHelper::VarUInt(intval($fee), true); // комиссия
		$d = IntHelper::VarUInt(intval($nonce), true); // нонс
		
		$f = $data; // дата
		$data_length = strlen($f);
		$data_length = ($data_length > 0)?$data_length / 2:0;
		$e = IntHelper::VarUInt(intval($data_length), true); // счетчик для даты

		$sign_text = $a.$b.$c.$d.$e.$f;

		if($this->debug)
		{
			echo '<h3>Sign Data Aray</h3>';
			var_dump([$a, $b, $c, $d, $e, $f]);
			echo '<h3>Sign Data</h3>';
			var_dump($sign_text);
		}
		
		return hex2bin($sign_text);
	}

	public function sign($sign_text, $private_key)
	{
		return $this->ecdsa->sign($sign_text, $private_key);
	}

	public function getConnectionAddress($node = null)
	{	
		if(isset($this->hosts[$node]) && !empty($this->hosts[$node]))
		{
			return $this->hosts[$node];
		}
		else
		{
			$node_url = null;
			$node_port = null;
			
			switch($node)
			{
				case 'PROXY':
					$node_url = sprintf(self::PROXY['url'], $this->net);
					$node_port = self::PROXY['port'];
				break;
				case 'TORRENT':
					$node_url = sprintf(self::TORRENT['url'], $this->net);
					$node_port = self::TORRENT['port'];
				break;
				default:
					// empty
				break;
			}

			if($node_url)
			{
				$list = dns_get_record($node_url, DNS_A);
				foreach($list as $val)
				{
					if($res = $this->checkHost($val['ip'].':'.$node_port))
					{
						return $val['ip'].':'.$node_port;
					}
				}
			}
		}

		return false;
	}

	private function checkHost($host)
	{
		if(!empty($host))
		{
			$curl = $this->curl;
			curl_setopt($curl, CURLOPT_URL, $host); 
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT, 1);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, '{"id":"1","method":"","params":[]}');
			curl_exec($curl);
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			if($code > 0 && $code < 500)
			{
				return true;
			}
		}

		return false;
	}

	private function queryProxy($method, $data = [])
	{
		try
		{
			$query = [
				'id' => time(),
				'method' => trim($method),
				'params' => $data
			];
			$query = json_encode($query);
			$url = $this->getConnectionAddress('PROXY');

			if($this->debug)
			{
				echo '<h3>Host PROXY:</h3>';
				var_dump($url);
				echo '<h3>Query PROXY:</h3>';
				var_dump($query);
			}

			if($url)
			{
				$curl = $this->curl;
				curl_setopt($curl, CURLOPT_URL, $url); 
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
				curl_setopt($curl, CURLOPT_TIMEOUT, 3);
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $query);

				$result = curl_exec($curl);
				$err = curl_error($curl);
				$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

				if($this->debug)
				{
					echo '<h3>Curl code PROXY:</h3>';
					var_dump($code);
					echo '<h3>Curl error PROXY:</h3>';
					var_dump($err);
					echo '<h3>Res PROXY:</h3>';
					var_dump($result);
				}
				$result = json_decode($result, true);
				return $result;
			}
			else
			{
				throw new Exception('The proxy service is not available. Maybe you have problems with DNS.');
			}
		}
		catch(Exception $e)
		{
			if($this->debug)
			{
				echo '<h3>Exception PROXY:</h3>';
				var_dump($e->getMessage());
			}
			else
			{
				throw new Exception($e->getMessage());
			}
		}

		return false;
	}

	private function queryTorrent($method, $data = [])
	{
		try
		{		
			$query = [
				'id' => time(),
				'method' => trim($method),
				'params' => $data
			];
			$query = json_encode($query);
			$url = $this->getConnectionAddress('TORRENT');

			if($this->debug)
			{
				echo '<h3>Host TORRENT:</h3>';
				var_dump($url);
				echo '<h3>Query TORRENT:</h3>';
				var_dump($query);
			}

			if($url)
			{
				$curl = $this->curl;
				curl_setopt($curl, CURLOPT_URL, $url); 
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
				curl_setopt($curl, CURLOPT_TIMEOUT, 3);
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_HTTPGET, false);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $query);

				$result = curl_exec($curl);
				if($this->debug)
				{
					echo '<h3>Res TORRENT:</h3>';
					var_dump($result);
				}

				$result = json_decode($result, true);
				return $result;
			}
			else
			{
				throw new Exception('The proxy service is not available. Maybe you have problems with DNS.');
			}
		}
		catch(Exception $e)
		{
			if($this->debug)
			{
				echo '<h3>Exception TORRENT:</h3>';
				var_dump($e->getMessage());
			}
			else
			{
				throw new Exception($e->getMessage());
			}
		}

		return false;
	}


}

function check_net_arg($args)
{
	if(empty($args['net']) || $args['net'] == null)
	{
		throw new Exception('net is empty', 1);
	}
	elseif(in_array($args['net'], ['main', 'dev', 'test']) == false)
	{
		throw new Exception('unsupported net value', 1);
	}
}

//============================================================================================//

try
{
	$args = [];
	if(is_cli())
	{
		parse_str(implode('&', array_slice($argv, 1)), $args);
	}
	else
	{
		$args = $_GET;
	}

	$args['method'] = isset($args['method']) && !empty($args['method'])?strtolower($args['method']):null;
	$args['net'] = isset($args['net']) && !empty($args['net'])?strtolower($args['net']):null;
	$args['address'] = isset($args['address']) && !empty($args['address'])?strtolower($args['address']):null;
	$args['hash'] = isset($args['hash']) && !empty($args['hash'])?strtolower($args['hash']):null;
	$args['to'] = isset($args['to']) && !empty($args['to'])?strtolower($args['to']):null;
	$args['value'] = isset($args['value']) && !empty($args['value'])?number_format($args['value'], 0, '', ''):0;
	$args['nonce'] = isset($args['nonce']) && !empty($args['nonce'])?intval($args['nonce']):0;

	if(empty($args['method']) || $args['method'] == null)
	{
		throw new Exception('method is empty', 1);
	}

	$crypto = new Crypto(new Ecdsa());
	//$crypto->debug = true;
	$crypto->net = $args['net'];

	switch($args['method'])
	{
		case 'generate':
			//check_net_arg($args);
			echo json_encode($crypto->generate());
		break;

		case 'fetch-balance':
			check_net_arg($args);
			if($crypto->checkAdress($args['address']) == false)
			{
				throw new Exception('invalid address value', 1);
			}

			echo json_encode($crypto->fetchBalance($args['address']));
		break;

		case 'fetch-history':
			check_net_arg($args);
			if($crypto->checkAdress($args['address']) == false)
			{
				throw new Exception('invalid address value', 1);
			}

			echo json_encode($crypto->fetchHistory($args['address']));
		break;

		case 'get-tx':
			check_net_arg($args);
			if(empty($args['hash']))
			{
				throw new Exception('hash is empty', 1);
			}

			echo json_encode($crypto->getTx($args['hash']));
		break;

		case 'get-list-address':
			echo json_encode($crypto->listAddress());
		break;

		case 'create-tx':
			// 
		break;

		case 'send-tx':
			check_net_arg($args);

			if(($keys = $crypto->readAddress($args['address'])) == false)
			{
				throw new Exception('address file not found', 1);
			}

			$nonce = $crypto->getNonce($args['address']);
			$sign_text = $crypto->makeSign($args['to'], intval($args['value']), intval($nonce), 0, '', 0);
			$sign = $crypto->sign($sign_text, $keys['private']);
			$res = $crypto->sendTx($args['to'], $args['value'], '', $nonce, '', $keys['public'], $sign);

			echo json_encode($res);
		break;

		default:
			throw new Exception('method not found', 1);
		break;
	}
}
catch(Exception $e)
{
	echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

echo PHP_EOL;
die();