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

There's an SQLite database in var folder, you can find the path in .env. 
It's currently empty.

## Testing
For unit tests, use command
```bash 
php bin/phpunit tests
```
  
## API
There's an API endpoint for user balance, you can access it at the following URL:

If you want to run the web server, run the followin command:
```bash
symfony server:start
```

After that, you can go to following URL:
```bash 
http://127.0.0.1:8000/api/user/{id}/balance
```

Note: The PORT might be different on your machine, so please get the PORT
from the Success message that is displayed after running the server:start command
