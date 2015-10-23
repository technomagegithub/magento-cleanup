<?php
class MagentoDbCleaner
{
    const DB_HOST = '127.0.0.1';
    const DB_USERNAME = 'root';
    const DB_PASSWORD = 'root';

    protected $_notAllowedDBs = array (
        "information_schema",
        "performance_schema"
    );

    protected $_tables = array (
        "dataflow_batch_export",
        "dataflow_batch_import",
        "log_customer",
        "log_quote",
        "log_summary",
        "log_summary_type",
        "log_url",
        "log_url_info",
        "log_visitor",
        "log_visitor_info",
        "log_visitor_online",
        "report_viewed_product_index",
        "report_compared_product_index",
        "report_event",
        "index_event",
        "catalog_compare_item"
   );

    public function run() {
        // Only Shell access
        (PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die('This script is designed to run in CLI mode.');
        
        echo "Are you sure you want to perform cleanup for all Magento databases? Type 'yes' to continue: ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        $response = trim($line);

        if(!($response == 'yes' || $response == 'y')) {
            echo "Aborting." . "\n";
            return;
        }

        echo "Performing Magento databases cleanup..." . "\n";
        echo "DB Cleaner" . "\n";
        echo "@verion 1.0" . "\n";

        $this->_process();
    }

    protected function _getPrefix($dbname) {
        $table = 'admin_user';
        $db = new PDO("mysql:host=".self::DB_HOST.";dbname=$dbname",self::DB_USERNAME,self::DB_PASSWORD);
        $sql = 'SHOW tables like :config';
        
        $q = $db->prepare($sql);
        $q->bindValue(':config', "%admin_user%");
        $q->execute();
        $data = $q->fetchAll();
        
        if ($data) {
            $data = explode('admin_user', $data[0][0]);
            return $data[0];
        }

        return '';
    }

    protected function _getAllDatabases() {
        $databases = array();
        $link = mysql_connect(self::DB_HOST, self::DB_USERNAME, self::DB_PASSWORD);
        $query = mysql_query("SHOW DATABASES");
        while ($row = mysql_fetch_assoc($query)) {
            if(!in_array($row['Database'], $this->_notAllowedDBs)) {
                $databases[] = $row['Database'];
            }
        }

        return $databases;
    }

    protected function _getDBQueries() {
        foreach ($this->_getAllDatabases() as $database) {
            $query = "SET foreign_key_checks = 0;\n";
            $dbPrefix = $this->_getPrefix($database);
            foreach ($this->_tables as $table) {
                $query .= "TRUNCATE {$dbPrefix}{$table};\n";
            }
            $query .= "SET foreign_key_checks = 1;";
            $databasesQuery[$database] = $query;
        }

        return $databasesQuery;
    }

    protected function _process() {
        $dbQueries = $this->_getDBQueries();

        foreach ($dbQueries as $dbName => $query) {
            echo "Database Name: {$dbName}" . "\n";
            $db = new PDO("mysql:host=".self::DB_HOST.";dbname=$dbName",self::DB_USERNAME,self::DB_PASSWORD);

            if (!$db) {
                echo "Database Error: {$dbName}" . "\n\n";
                continue;
            }

            echo "Query run" . "\n";
            echo $query;
            $q = $db->prepare($query);

            if ($q->execute()) {
                echo "Completed" . "\n\n";
            } else {
                echo "Error: " . "\n\n";
                var_dump($q->errorInfo());
                echo "\n\n";
            }
            echo "------------------------------------------------------" . "\n\n";;
        }
    }
}
 
$app = new MagentoDbCleaner();
$app->run();