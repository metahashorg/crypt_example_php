# Crypt example PHP
This repository contains PHP scripts that enable to generate MetaHash addresses, check its balance and see the full transaction history. Besides, `crypt_example.php` and `crypt_example_bin.php` scripts describe methods allowing to create and send transactions as well as to obtain information on transaction via its hash. To learn more about all operations listed above please read the following articles: [Getting started with Metahash network](https://support.metahash.org/hc/en-us/articles/360002712193-Getting-started-with-Metahash-network), [Creating transactions](https://support.metahash.org/hc/en-us/articles/360003271694-Creating-transactions) and [Operations with MetaHash address](https://support.metahash.org/hc/en-us/articles/360008382213-Operations-with-MetaHash-address). 

Script `crypt_example.php` works with `mdanter/ecc` library. Script `crypt_example_bin.php` is written using our driver for PHP [https://github.com/metahashorg/php-mhcrypto](https://github.com/metahashorg/php-mhcrypto). 

## Requirements

For `crypt_example.php`
- PHP 7.1+
- composer
- ext-gmp
- ext-curl
- mdanter/ecc

For `crypt_example_bin.php`
- PHP 7.1+
- ext-curl
- ext-mhcrypto (see [https://github.com/metahashorg/php-mhcrypto](https://github.com/metahashorg/php-mhcrypto))

## Dependencies

For `crypt_example.php`
1) open the folder with `crypt_example.php` script
2) install  `composer require mdanter/ecc:0.5.0`
3) create the folder `data` and set Read and Write permissions (unix chmod 0777)

For `crypt_example_bin.php`
1) open the folder with `crypt_example_bin.php` script
2) install `ext-mhcrypto`. For more details about installing `ext-mhcrypto`, please see [https://github.com/metahashorg/php-mhcrypto](https://github.com/metahashorg/php-mhcrypto).
3) create the folder `data` and set Read and Write permissions (unix chmod 0777)

## Usage

```shell
php crypt_example.php [params]
```

```shell
php crypt_example_bin.php [params]
```

## List of methods
All methods are present in `crypt_example.php` and `crypt_example_bin.php` scripts. Below are examples for the script `crypt_example.php`.

#### generate
Generate MH address

Example:
```shell
php crypt_example.php method=generate
```

#### fetch-balance
Get balance for MH address

Params:
- net - network (main, dev)
- address - MH address

Example:
```shell
php crypt_example.php method=fetch-balance net=dev address=0x003d3b27f544d1dc03d6802e6379fdcfc25e0b73272b62496b
```
#### fetch-history
Get history for MH address

Params:
- net - network (main, dev)
- address - MH address

Example:
```shell
php crypt_example.php method=fetch-history net=dev address=0x003d3b27f544d1dc03d6802e6379fdcfc25e0b73272b62496b
```

#### get-tx
Get transaction information by hash

Params:
- net - network (main, dev)
- hash - transaction hash

Example:
```shell
php crypt_example.php method=get-tx net=dev hash=ee0e11b793ff5a5b0d6954f0da4964ceb53f9887480e9a5e42608830ed401963
```

#### send-tx
Create and send transaction

Params:
- net - network (main, dev)
- address - MH address (from created addresses)
- to - MH address
- value

Example:
```shell
php crypt_example.php method=send-tx net=dev address=0x003d3b27f544d1dc03d6802e6379fdcfc25e0b73272b62496b to=0x00525d3f6326549b8974ef669f8fced6153109ba60d52d547d value=1000 
```
