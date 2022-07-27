# Test

## Requirements
PHP 8.1  
Symfony  
FOS RestBundle  
PHPUNIT  

## Installation

Use Composer to install the project.

```bash
composer install
```

If you need to run migrations, use the commands:
```bash
php bin/console make:migration
```

You might also need to run:
```bash
console doctrine:schema:update
```

## Local server

If you want to run the web server, run the following command:
```bash
symfony server:start
```

## API


There's an API endpoint for user balance, you can access it at the following URL:
```
http://127.0.0.1:8000/api/user/{id}/balance/{fromDate}/{toDate}
```

Note: The PORT might be different on your machine, so please get the PORT
from the Success message that is displayed after running the server:start command


## Testing
For integration tests, use command
```bash 
php bin/phpunit tests/Application/Controller/
```

It uses the same database, and it has Fixtures to populate the database.