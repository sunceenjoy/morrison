# Introduction
This is to demonstrate both frontend and backend development skills. It's a simple voting page. A user votes by submitting with a valid email address and sticker. An email will be sent to the voter. 

The code is based on PHP Symfony2 Framework, using Mysql as the database storage and Redis as the cache server. It's browsers compatible and mobile compatible. It is able to handle numerous of requests smoothly.

Demo: https://morrison.mmyyabb.com

# Directory Structure
  - ***app/config***: config files dir.
  - ***app/resources***: services, routings or other php related resources 
  - ***app/src***: main source files
  - ***bin***: command entry file
  - ***database-migration***: database migration
  - ***res/cache***: cache dir
  - ***res/logs***: logs dir
  - ***res/styles***: SCSS files dir
  - ***res/template***: twig template files dir
  - ***webroot***: web root dir

# Implementation
In order to handle lots of requests without bogging down or keeping website users waiting. From ***coding perspective***, We need to remove the time consuming parts: 
  -  retrieving email address from database. (Usually, this is not an issue when having the proper indexs. However, on a high concurrency, high load web application, it still could be an issue). 
  -  sending email. This is definitely a time consuming process which could negatively impact the performance in a high concurrency, high load web application or even crash the services.
   
So, I use redis as memory storage for email address to solve the first issue, use a mysql table as queue to solve the second one. (We also can use RabbitMQ which is a professional Queue to handle these situations).

# Request Lifecycle
  - Front End:
    1. A User opens vote page (/)
    2. This user sends a post to /vote/post page
    2. Route the request to VoteController
    3. Check out user input
    4. Check out email address and ip restrictions
    5. Add a new vote record
    6. Add a new email into email send queue
    7. Redirect this page to vote page and display vote results.
  - Back End:
    1.  Run the sending emails command in the backend to send emails from queue.

# Scenario
So far, my implement is able to handle large requests. But to make this service nicer, stabler and available, there are still things we can do:
 - ***From business perspective***:
    1.  Add restriction to a single ip to prevent from fake vote data.
    2.  Add email verification, make sure those are real emails.
    3.  Add CAPTCHA for a high level vote to prevent from fake voting.
 - ***From web architect perspective:***
    1. Use Reverse Proxy, distributed servers to do load balance. (tool:[nginx](https://www.nginx.com/resources/admin-guide/reverse-proxy/)） 
    2. Add master-slave mode for database (tool: [phxsql](https://github.com/tencent-wechat/phxsql))
    3. Add master-slave mode for redis(tool: [sentinel](https://redis.io/topics/sentinel))
    4. Use CDN to cache all static resources: css, js, images. (Alternative tool: [varnish](https://varnish-cache.org))
    5. Use third party's sending email service to send emails. Because it could be an issue if we send huge amount of emails from a  single server/ip.

In general, we need to improve are user experience, set up load balance, automated fail-over for all services, cache slow queries, add proper indexs for databases and write proper queries. Also set up log system.


### Installation
1.Add write rule & ENV

For apache
```sh
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} !-f
RewriteRule ^(.*)$ /index.php$1 [L]
......
SetEnv MORRISON_ENV morrison_dev
```
For nginx
```sh
location ~ \.php$|/index.php/|^/status$ {
fastcgi_pass   unix:/var/run/php/php5.6-fpm.sock;
fastcgi_index  index.php;
fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
fastcgi_param  MORRISON_ENV morrison_prod;
include        fastcgi_params;
fastcgi_read_timeout 600;
}
```
2.Copy the code to local server
```
$ git clone https://github.com/sunceenjoy/morrison
# Enter into project dir
$ cd morrison
```

3.Import migration.sql into database
```sh
# Change username to your db username
$ mysql -u username -p < ./database-migration/migration.sql
```
4.Config databse, redis
```sh
$ vim ./app/config/prod/app.ini
```
5.Set up cache, logs privileges
```sh
$ mkdir -m 777 ./res/cache
$ mkdir -m 777 ./res/logs
```

6.Install php dependences.
```sh
$ composer install
```
Now, we are all set. 
To send emails, we need to run another command:
```sh
$  MORRISON_ENV=morrison_prod php bin/console.php cron:send-email
/**
   dry run: MORRISON_ENV=morrison_prod php bin/console.php cron:send-email --dry-run
*/
```

### Unit test:
```sh
$  phpunit -c phpunit.xml.dist
```
