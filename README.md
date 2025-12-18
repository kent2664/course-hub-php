
# README


### Project description 

This is a PHP backend application for Course-Hub. It includes functions to manage course and user information.


### Setup instructions.
1. make the database `course-hub-db`.
2. import data from `Assets\Exported_database.sql`
3. webconfing should be /service and sample code is here.

        define("DB_SERVER","localhost");
        define("DB_USER","PHP_AGENT");
        define("DB_PASS","kjU.i8mM@-AQVRu(");
        define("DB_NAME","course-hub-db");

        $pdo = new PDO(
        "mysql:host=".DB_SERVER.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

### Main endpoints

