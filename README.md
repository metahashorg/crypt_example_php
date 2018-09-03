# Crypt example PHP
This repository contains PHP scripts that enable to generate MetaHash addresses, check its balance and see the full transaction history. Besides, `crypt_example.php` script describes methods allowing to create and send transactions as well as to obtain information on transaction via its hash. To learn more about all operations listed above please read the following articles: [Getting started with Metahash network](https://support.metahash.org/hc/en-us/articles/360002712193-Getting-started-with-Metahash-network), [Creating transactions](https://support.metahash.org/hc/en-us/articles/360003271694-Creating-transactions) and [Operations with MetaHash address](https://support.metahash.org/hc/en-us/articles/360008382213-Operations-with-MetaHash-address). 

There are 2 ways of working with the script:
1) Using the extension for php `mhcrypto`:
- high speed of working (generation of keys / addresses / signatures, signature validation);
- no additional files in the project folder;
- requiring additional steps to install and configure the extension.
2) Using the php library of `mdanter/ecc`:
- simple installation;
- additional files in the project folder.

You can use the path that suits you.

## Requirements

#### Basic requirements
- PHP 7.1+
- ext-gmp
- ext-curl

#### Additional requirements for `mdanter/ecc` library

- composer
- mdanter/ecc (0.5.0)

#### Additional requirements for `mhcrypto` extension

- ext-mhcrypto (see [https://github.com/metahashorg/php-mhcrypto](https://github.com/metahashorg/php-mhcrypto))

## Dependencies

For ubuntu 14.x 16.x 18.x follow these steps:

First of all you need to install php repository with php 7.x :

1) Install add-apt-repository scripts and add repo
```shell
sudo apt-get install -y python-software-properties
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update -y
```

2) Install php-7.2 with needed mondules
```shell
sudo apt-get install php7.2-cli php7.2-curl php7.2-dev php7.2-gmp
```

3) For `mdanter/ecc` library

	a) Install composer
	```shell
	sudo apt-get install composer
	```
	b) Install mdanter/ecc with composer
	```shell
	composer require mdanter/ecc:0.5.0
	```

	Sample output:
	```shell
	Using version 0.5.0 for mdanter/ecc
	./composer.json has been created
	Loading composer repositories with package information
	Updating dependencies (including require-dev)
	  - Installing fgrosse/phpasn1 (2.0.1)
	    Downloading: 100%

	  - Installing mdanter/ecc (v0.5.0)
	    Downloading: 100%

	fgrosse/phpasn1 suggests installing php-curl (For loading OID information from the web if they have not bee defined statically)

	mdander/ecc will be install in vendor/ folder in current directory
	```

4) For `mhcrypto` extension 

	a) Install `ext-mhcrypto`. For more details about installing `ext-mhcrypto`, please see [https://github.com/metahashorg/php-mhcrypto](https://github.com/metahashorg/php-mhcrypto).

5) Create the folder `data` and set Read and Write permissions (unix chmod 0777)


## Usage

```shell
php crypt_example.php [params]
```

## List of methods
All methods are present in `crypt_example.php`.

The script works with the following networks:
- `test` Test network with the ability to create an address with a balance
- `dev` Test network
- `main` main network

#### generate
Generate MH address

Example:
```shell
php crypt_example.php method=generate
```

#### fetch-balance
Get balance for MH address

Params:
- net - network
- address - MH address

Example:
```shell
php crypt_example.php method=fetch-balance net=test address=0x003d3b27f544d1dc03d6802e6379fdcfc25e0b73272b62496b
```
#### fetch-history
Get history for MH address

Params:
- net - network
- address - MH address

Example:
```shell
php crypt_example.php method=fetch-history net=test address=0x003d3b27f544d1dc03d6802e6379fdcfc25e0b73272b62496b
```

#### get-tx
Get transaction information by hash

Params:
- net - network
- hash - transaction hash

Example:
```shell
php crypt_example.php method=get-tx net=test hash=ee0e11b793ff5a5b0d6954f0da4964ceb53f9887480e9a5e42608830ed401963
```

#### send-tx
Create and send transaction

Params:
- net - network
- address - MH address (from created addresses)
- to - MH address
- value

Example:
```shell
php crypt_example.php method=send-tx net=test address=0x003d3b27f544d1dc03d6802e6379fdcfc25e0b73272b62496b to=0x00525d3f6326549b8974ef669f8fced6153109ba60d52d547d value=1000 
```
