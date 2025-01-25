## 1BRC in PHP
This is a PHP Benchmark for the [1 Billion Rows Challenge](https://github.com/gunnarmorling/1brc)  

**Explanation post: [https://gianlucafabrizi.dev/blog/posts/1brc-php/](https://gianlucafabrizi.dev/blog/posts/1brc-php/)**  

`src/calculate_*.php` are PHP script with subtle changes, used to benchmark different approaches to the problem.  
  
In `src/zts/` you can find a script that takes advantage of multi thread in PHP; to run it you have to use a zts-safe version of PHP.  
You can install one from binary, or you can compile from source; in debian you can compile the latest version of PHP (8.4.3 at the moment) this way:
```shell
# Install build tools and libraries needed
sudo apt-get -y install build-essential autoconf libtool bison re2c pkg-config git libxml2-dev libssl-dev

# Clone and build a stripped down version of PHP with ZTS support
git clone https://github.com/php/php-src.git --branch=PHP-8.4.3 --depth=1
cd php-src/
./buildconf
./configure --prefix=/opt/php8.4-zts --with-config-file-path=/opt/php8.4-zts/etc/php --disable-all --disable-ipv6 --disable-cgi --disable-phpdbg --enable-zts --enable-xml --with-libxml --with-pear --with-openssl
make -j8
./sapi/cli/php -v
sudo make install

# Install `parallel` module from PECL
sudo /opt/php8.4-zts/bin/pecl channel-update pecl.php.net
sudo /opt/php8.4-zts/bin/pecl install parallel
sudo mkdir -p /opt/php8.4-zts/etc/php/conf.d
echo 'extension=parallel.so' | sudo tee -a /opt/php8.4-zts/etc/php/php.ini
echo 'memory_limit=-1' | sudo tee -a /opt/php8.4-zts/etc/php/php.ini

# Verify module installation
/opt/php8.4-zts/bin/php -i | grep parallel
```

`run.sh` and `run-zts.sh` are script used to bulk benchmark the PHP scripts. In these shell scripts you can specify the number of rows of the measurements file and how many times to run each PHP script.     
To run them, you need to install `perf` tool; in debian these are the commands:
```shell
sudo apt-get install -y linux-perf
echo "kernel.perf_event_paranoid=-1" | sudo tee -a /etc/sysctl.conf
# in order for the last change to be effective, you need to reboot
sudo reboot
```

`deploy.sh` is a script written to make easier (and faster) the benchmark on a remote server (in my case on an AWS EC2 instance).  
