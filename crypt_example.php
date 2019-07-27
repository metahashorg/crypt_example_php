<?php
try
{
	define('MHCRYPTO', false);
	define('ROOT_DIR', __DIR__);
	define('DATA_DIR', ROOT_DIR.'/data');

	if(file_exists(DATA_DIR) == false)
		throw new Exception('`data` directory not found in '.ROOT_DIR);

	if(floatval(phpversion()) < 7.1)
		throw new Exception('requirements PHP 7.1+, current '.phpversion());

	if(extension_loaded('curl') == false)
		throw new Exception('php-curl extension not loaded ');

	if(MHCRYPTO)
	{
		if(extension_loaded('mhcrypto') == false)
			throw new Exception('mhcrypto extension not loaded');
	}
	else
	{
		if(extension_loaded('gmp') == false)
			throw new Exception('php-gmp extension not loaded ');

		if(file_exists(ROOT_DIR.'/vendor/autoload.php') == false)
			throw new Exception('`vendor/autoload.php` not found  in '.ROOT_DIR);

		include_once 'vendor/autoload.php';

		if(file_exists(ROOT_DIR.'/vendor/mdanter/ecc/src/EccFactory.php') == false)
			throw new Exception('`mdanter/ecc` not found. Please run composer command `composer require mdanter/ecc:0.4.2`');
	}
}
catch(Exception $e)
{
	echo json_encode(['error' => true, 'message' => $e->getMessage()]);
	echo PHP_EOL;
	die();
}

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Crypto\Signature\Signer;
use Mdanter\Ecc\Crypto\Signature\SignHasher;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\Signature\DerSignatureSerializer;
use Mdanter\Ecc\Random\RandomGeneratorFactory;

class Ecdsa
{
	private $adapter;
	private $generator;

	public function __construct()
	{
		if(!MHCRYPTO)
		{
			$this->adapter = EccFactory::getAdapter();
			$this->generator = EccFactory::getSecgCurves()->generator256k1();
		}
	}

	public function getKey()
	{
		$result = [
			'private' => null,
			'public' => null,
			'address' => null
		];

		if(MHCRYPTO)
		{
			mhcrypto_generate_wallet($result['private'], $result['public'], $result['address']);
			foreach($result as &$val)
			{
				$val = $this->to_base16($val);
			}
		}
		else
		{
			$private = $this->generator->createPrivateKey();
			$serializer_private = new DerPrivateKeySerializer($this->adapter);
			$data_private = $serializer_private->serialize($private);
			$result['private'] = '0x'.bin2hex($data_private);

			$public = $private->getPublicKey();
			$serializer_public = new DerPublicKeySerializer($this->adapter);
			$data_public = $serializer_public->serialize($public);
			$result['public'] = '0x'.bin2hex($data_public);
		}

		return $result;
	}

	public function privateToPublic($private_key)
	{
		$result = null;

		if(MHCRYPTO)
		{
			$public_key = null;
			mhcrypto_generate_public($this->parse_base16($private_key), $public_key);
			$result = $this->to_base16($public_key);
		}
		else
		{
			$serializer_private = new DerPrivateKeySerializer($this->adapter);
			$private_key = $this->parse_base16($private_key);
			$private_key = hex2bin($private_key);
			$key = $serializer_private->parse($private_key);

			$public = $key->getPublicKey();
			$serializer_public = new DerPublicKeySerializer($this->adapter);
			$data_public = $serializer_public->serialize($public);
			$result = '0x'.bin2hex($data_public);
		}

		return $result;
	}

	public function sign($data, $private_key, $rand = false, $algo = 'sha256')
	{
		$sign = null;

		if(MHCRYPTO)
		{
			mhcrypto_sign_text($sign, $this->parse_base16($private_key), $data);
		}
		else
		{
			$serializer_private = new DerPrivateKeySerializer($this->adapter);
			$private_key = $this->parse_base16($private_key);
			$private_key = hex2bin($private_key);
			$key = $serializer_private->parse($private_key);

			$hasher = new SignHasher($algo, $this->adapter);
			$hash = $hasher->makeHash($data, $this->generator);

			if(!$rand)
			{
				$random = RandomGeneratorFactory::getHmacRandomGenerator($key, $hash, $algo);
			}
			else
			{
				$random = RandomGeneratorFactory::getRandomGenerator();
			}

			$randomK = $random->generate($this->generator->getOrder());
			$signer = new Signer($this->adapter);
			$signature = $signer->sign($key, $hash, $randomK);

			$serializer = new DerSignatureSerializer();
			$sign = $serializer->serialize($signature);
		}

		return '0x'.bin2hex($sign);
	}

	public function verify($sign, $data, $public_key, $algo = 'sha256')
	{
		$result = false;

		if(MHCRYPTO)
		{
			$result = mhcrypto_check_sign_text($this->hex2bin($sign), $this->parse_base16($public_key), $data);
		}
		else
		{
			$serializer = new DerSignatureSerializer();
			$serializer_public = new DerPublicKeySerializer($this->adapter);

			$public_key = $this->parse_base16($public_key);
			$public_key = hex2bin($public_key);
			$key = $serializer_public->parse($public_key);

			$hasher = new SignHasher($algo);
			$hash = $hasher->makeHash($data, $this->generator);

			$sign = $this->parse_base16($sign);
			$sign = hex2bin($sign);
			$serialized_sign = $serializer->parse($sign);
			$signer = new Signer($this->adapter);
			$check = $signer->verify($key, $serialized_sign, $hash);

			$result = ($signer->verify($key, $serialized_sign, $hash))?true:false;
		}

		return $result;
	}

	public function getAdress($key, $net = '00')
	{
		$address = null;

		if(MHCRYPTO)
		{
			mhcrypto_generate_address($key, $address);
		}
		else
		{
			$code = '';

			$serializer_public = new DerPublicKeySerializer($this->adapter);
			$key = $this->parse_base16($key);
			$key = hex2bin($key);
			$key = $serializer_public->parse($key);
			$x = gmp_strval($key->getPoint()->getX(), 16);
			$xlen = 64 - strlen($x);
			$x = ($xlen > 0)?str_repeat('0', $xlen).$x:$x;
			$y = gmp_strval($key->getPoint()->getY(), 16);
			$ylen = 64 - strlen($y);
			$y = ($ylen > 0)?str_repeat('0', $ylen).$y:$y;

			$code = '04'.$x.$y;
			$code = hex2bin($code);
			$code = hex2bin(hash('sha256', $code));
			$code = $net.hash('ripemd160', $code);
			$code = hex2bin($code);
			$hash_summ = hex2bin(hash('sha256', $code));
			$hash_summ = hash('sha256', $hash_summ);
			$hash_summ = substr($hash_summ, 0, 8);
			$address = bin2hex($code).$hash_summ;
		}

		return $this->to_base16($address);
	}

	public function checkAdress($address)
	{
		if(!empty($address))
		{
			if(MHCRYPTO)
			{
				return mhcrypto_check_address($this->parse_base16($address));
			}
			else
			{
				if(strlen($this->parse_base16($address))%2) return false;

				$address_hash_summ = substr($address, strlen($address) - 8, 8);
				$code = substr($address, 0, strlen($address) - 8);
				$code = substr($code, 2);
				$code = hex2bin($code);
				$hash_summ = hex2bin(hash('sha256', $code));
				$hash_summ = hash('sha256', $hash_summ);
				$hash_summ = substr($hash_summ, 0, 8);

				if($address_hash_summ === $hash_summ)
				{
					return true;
				}
			}
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

function str2hex($string)
{
	return implode(unpack("H*", $string));
}

function hex2str($hex)
{
	return pack("H*", $hex);
}

class Crypto
{
	private $ecdsa = null;
	public $debug = false;
	public $net = null;

	private $curl = null;
	private $proxy = ['url' => 'proxy.net-%s.metahashnetwork.com', 'port' => 9999];
	private $torrent = ['url' => 'tor.net-%s.metahashnetwork.com', 'port' => 5795];
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

	public function create($address)
	{
		try
		{
			if($host = $this->getConnectionAddress('PROXY'))
			{
				$host = $host.'/?act=addWallet&p_addr='.$address;
				$curl = $this->curl;
				curl_setopt($curl, CURLOPT_URL, $host);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
				curl_setopt($curl, CURLOPT_TIMEOUT, 2);
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_HTTPGET, false);

				$result = curl_exec($curl);
				if(strstr($result, 'Transaction accapted.'))
				{
					return true;
				}
			}
		}
		catch(Exception $e)
		{
			//
		}

		return false;
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
					$node_url = sprintf($this->proxy['url'], $this->net);
					$node_port = $this->proxy['port'];
				break;
				case 'TORRENT':
					$node_url = sprintf($this->torrent['url'], $this->net);
					$node_port = $this->torrent['port'];
				break;
				default:
					// empty
				break;
			}

			if($node_url)
			{
				$list = dns_get_record($node_url, DNS_A);
				$host_list = [];
				foreach($list as $val)
				{
					switch($node)
					{
						case 'PROXY':
							if($res = $this->checkHost($val['ip'].':'.$node_port))
							{
								$host_list[$val['ip'].':'.$node_port] = 1;
							}
						break;
						case 'TORRENT':
							$host_list[$val['ip'].':'.$node_port] = $this->torGetLastBlock($val['ip'].':'.$node_port);
						break;
						default:
							// empty
						break;
					}
				}

				arsort($host_list);
				$keys = array_keys($host_list);
				if(count($keys))
				{
                    $this->hosts[$node] = $keys[0];
                    return $this->hosts[$node];
				}
			}
		}

		return false;
	}

	private function torGetLastBlock($host)
	{
		if(!empty($host))
		{
			$curl = $this->curl;
			curl_setopt($curl, CURLOPT_URL, $host);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT, 1);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, '{"id":"1","method":"get-count-blocks","params":[]}');
			$res = curl_exec($curl);
			$res = json_decode($res, true);

			if(isset($res['result']['count_blocks']))
			{
				return intval($res['result']['count_blocks']);
			}
		}

		return 0;
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
	$args['fee'] = '';//isset($args['fee']) && !empty($args['fee'])?number_format($args['fee'], 0, '', ''):0;
	$args['data'] = isset($args['data']) && !empty($args['data'])?trim($args['data']):'';
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
			$result = $crypto->generate();
			$crypto->net = 'test';
			$crypto->create($result['address']);
			echo json_encode($result);
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

			// if($crypto->net != 'main')
			// {
				$data_len = strlen($args['data']);
				if($data_len > 0)
				{
					$args['fee'] = $data_len;
					$args['data'] = str2hex($args['data']);
				}
			// }
			// else
			// {
			// 	$args['data'] = '';
			// }

			$sign_text = $crypto->makeSign($args['to'], strval($args['value']), strval($nonce), strval($args['fee']), $args['data']);
			$sign = $crypto->sign($sign_text, $keys['private']);
			$res = $crypto->sendTx($args['to'], $args['value'], $args['fee'], $nonce, $args['data'], $keys['public'], $sign);

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
